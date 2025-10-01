<?php

namespace App\Jobs;

use App\Enums\OrderStatusEnum;
use App\Mail\CheckoutCompleted;
use App\Mail\NewOrderMail;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessCheckoutSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $session) {}

    public function handle(): void
    {
        $pi = $this->session['payment_intent'] ?? null;

        $orders = Order::query()
            ->with(['orderItems.product', 'vendorUser.vendor'])
            ->where('stripe_session_id', $this->session['id'])
            ->get();

        if ($orders->isEmpty()) {
            Log::warning("⚠ No orders found for session", [
                'session_id' => $this->session['id'],
            ]);
            return;
        }

        $allProductsToDelete = [];
        $userId = null;

        foreach ($orders as $order) {
            try {
                $order->payment_intent = $pi;
                $order->status = OrderStatusEnum::Paid->value;
                $order->save();

                $userId = $order->user_id;

                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    $options = $orderItem->variation_type_option_ids;

                    if ($product) {
                        if ($options) {
                            sort($options);
                            $variation = $product->variations()
                                ->whereJsonContains('variation_type_option_ids', $options)
                                ->first();

                            if ($variation && $variation->quantity !== null) {
                                $variation->quantity -= $orderItem->quantity;
                                $variation->save();
                            }
                        } elseif ($product->quantity !== null) {
                            $product->quantity -= $orderItem->quantity;
                            $product->save();
                        }
                    }

                    $allProductsToDelete[] = $orderItem->product_id;
                }

                // vendor email (safe try/catch)
                if ($order->vendorUser && $order->vendorUser->email) {
                    try {
                        Mail::to($order->vendorUser->email)
                            ->queue(new NewOrderMail($order));
                    } catch (\Throwable $e) {
                        Log::error("❌ Failed to send vendor mail", [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

            } catch (\Throwable $e) {
                Log::error("❌ Failed processing order", [
                    'order_id' => $order->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // cart cleanup (never skipped)
        if ($userId && !empty($allProductsToDelete)) {
            try {
                $deleted = CartItem::query()
                    ->where('user_id', $userId)
                    ->whereIn('product_id', $allProductsToDelete)
                    ->where('saved_for_later', false)
                    ->delete();

                Log::info("✅ Cart items deleted", [
                    'user_id' => $userId,
                    'deleted_count' => $deleted,
                ]);
            } catch (\Throwable $e) {
                Log::error("❌ Failed to delete cart items", [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // customer email (safe try/catch)
        try {
            if ($orders->count() > 0 && $orders[0]->user && $orders[0]->user->email) {
                Mail::to($orders[0]->user->email)
                    ->queue(new CheckoutCompleted($orders));
            }
        } catch (\Throwable $e) {
            Log::error("❌ Failed to send customer mail", [
                'session_id' => $this->session['id'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
