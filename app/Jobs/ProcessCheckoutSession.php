<?php

namespace App\Jobs;

use App\Enums\OrderStatusEnum;
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

    public array $session;

    public function __construct(array $session)
    {
        $this->session = $session;
    }

    public function handle(): void
    {
        $pi = $this->session['payment_intent'] ?? null;

        $orders = Order::with(['orderItems.product', 'vendorUser.vendor', 'user'])
            ->where('stripe_session_id', $this->session['id'] ?? null)
            ->get();

        if ($orders->isEmpty()) {
            Log::warning("No orders found for session", ['session_id' => $this->session['id'] ?? null]);
            return;
        }

        $allProductsToDelete = [];
        $userId = null;

        foreach ($orders as $order) {
            try {
                // Mark order as paid
                $order->payment_intent = $pi;
                $order->status = OrderStatusEnum::Paid->value;
                $order->save();

                $userId = $order->user_id;

                // Reduce stock
                foreach ($order->orderItems ?? [] as $orderItem) {
                    $product = $orderItem->product;
                    $options = $orderItem->variation_type_option_ids;

                    if ($product) {
                        if ($options) {
                            sort($options);
                            $variation = $product->variations()
                                ->whereJsonContains('variation_type_option_ids', $options)
                                ->first();

                            if ($variation && $variation->quantity !== null) {
                                $variation->quantity -= $orderItem->quantity ?? 0;
                                $variation->save();
                            }
                        } elseif ($product->quantity !== null) {
                            $product->quantity -= $orderItem->quantity ?? 0;
                            $product->save();
                        }
                    }

                    $allProductsToDelete[] = $orderItem->product_id;
                }

                // Send vendor email
                if (optional($order->vendorUser)->email) {
                    try {
                        Mail::to($order->vendorUser->email)
                            ->queue(new NewOrderMail($order->id));

                        Log::info("NewOrderMail queued successfully", [
                            'order_id' => $order->id,
                            'vendor_email' => $order->vendorUser->email,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error("Failed to queue NewOrderMail", [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

            } catch (\Throwable $e) {
                Log::error("Failed processing order", [
                    'order_id' => $order->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove items from cart
        if ($userId && !empty($allProductsToDelete)) {
            try {
                $deleted = CartItem::query()
                    ->where('user_id', $userId)
                    ->whereIn('product_id', $allProductsToDelete)
                    ->where('saved_for_later', false)
                    ->delete();

                Log::info("Cart items deleted", [
                    'user_id' => $userId,
                    'deleted_count' => $deleted,
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to delete cart items", [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("ProcessCheckoutSession completed for session", [
            'session_id' => $this->session['id'] ?? null,
            'orders' => $orders->pluck('id')->toArray(),
        ]);
    }
}
