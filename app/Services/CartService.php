<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartService
{
    private ?array $cachedCartItems = null;

    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365;

    public function addItemToCart(Product $product, int $quantity = 1, ?array $optionIds = null)
    {
        if ($optionIds === null) {
            $optionIds = $product->variationTypes
                ->mapWithKeys(fn (VariationType $type) => [$type->id => $type->options[0]?->id])
                ->toArray();
        }

        $price = $product->getPriceForOptions($optionIds);

        if (Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, ?array $optionIds = null)
    {
        if (Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    public function removeItemFromCart(int $productId, ?array $optionIds = null)
    {
        if (Auth::check()) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemsFromCookies($productId, $optionIds);
        }
    }

    public function getCartItems(): array
    {
        try {
            if ($this->cachedCartItems === null) {
                $cartItems = Auth::check()
                    ? $this->getCartItemsFromDatabase()
                    : $this->getCartItemsFromCookies();

                $productIds = collect($cartItems)->pluck('product_id');
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];

                foreach ($cartItems as $cartItem) {
                    $product = data_get($products, $cartItem['product_id']);
                    if (!$product) continue;

                    $optionInfo = [];
                    $options = VariationTypeOption::with('variationType')
                        ->whereIn('id', $cartItem['option_ids'] ?? [])
                        ->get()
                        ->keyBy('id');

                    $imageUrl = null;

                    foreach ($cartItem['option_ids'] ?? [] as $option_id) {
                        $option = data_get($options, $option_id);
                        if (!$option) continue;

                        if (!$imageUrl) {
                            $imageUrl = $option->getFirstMediaUrl('images');
                        }

                        $optionInfo[] = [
                            'id' => $option_id,
                            'name' => $option->name,
                            'type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name
                            ]
                        ];
                    }

                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'] ?? [],
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('images'),
                        'user' => [
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name ?? ''
                        ]
                    ];
                }

                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return [];
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_column($this->getCartItems(), 'quantity'));
    }

    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->getCartItems() as $item) {
            $total += $item['quantity'] * $item['price']; // multiply, not add
        }
        return $total;
    }

    // Database methods
    protected function saveItemToDatabase(int $productId, int $quantity, float $price, array $optionIds): void
    {
        ksort($optionIds);
        $userId = Auth::id();

        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', $optionIds)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,
            ]);
        }
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds): void
    {
        $userId = Auth::id();
        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', $optionIds)
            ->update(['quantity' => $quantity]);
    }

    protected function removeItemFromDatabase(int $productId, array $optionIds): void
    {
        $userId = Auth::id();
        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', $optionIds)
            ->delete();
    }

    // Cookie methods
    protected function saveItemToCookies(int $productId, int $quantity, float $price, array $optionIds): void
    {
        ksort($optionIds);
        $cartItems = $this->getCartItemsFromCookies();
        $key = $productId . '_' . implode('-', $optionIds);

        if (isset($cartItems[$key])) {
            $cartItems[$key]['quantity'] += $quantity;
            $cartItems[$key]['price'] = $price;
        } else {
            $cartItems[$key] = [
                'id' => Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds
            ];
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds): void
    {
        ksort($optionIds);
        $cartItems = $this->getCartItemsFromCookies();
        $key = $productId . '_' . implode('-', $optionIds);

        if (isset($cartItems[$key])) {
            $cartItems[$key]['quantity'] = $quantity;
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function removeItemsFromCookies(int $productId, array $optionIds): void
    {
        ksort($optionIds);
        $cartItems = $this->getCartItemsFromCookies();
        $key = $productId . '_' . implode('-', $optionIds);

        unset($cartItems[$key]);
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    // Getters
    protected function getCartItemsFromDatabase(): array
    {
        return CartItem::where('user_id', Auth::id())->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'option_ids' => $item->variation_type_option_ids,
            ];
        })->toArray();
    }

    protected function getCartItemsFromCookies(): array
    {
        return json_decode(Cookie::get(self::COOKIE_NAME, '{}'), true) ?? [];
    }
}
