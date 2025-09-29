<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Stripe checkout success page.
     */
    public function success(Request $request)
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');
        $orders = Order::where('stripe_session_id', $session_id)
            ->get();
        
            if($orders->count() === 0){
                abort(404);
            }

            foreach($orders as $order){
                if($order->user_id !== $user->id){
                    abort(403);
                }
            }

            return Inertia::render('Stripe/Success', [
               'orders' => OrderViewResource::collection($orders)->collection->toArray(),
            ]);
    }

    /**
     * Stripe checkout failure page.
     */
    public function failure()
    {
        return view('checkout.failure');
    }

    /**
     * Stripe webhook handler.
     */
    public function webhook(Request $request)
    {
        $stripe = new StripeClient(config('app.stripe_secret_key'));
        $endpointSecret = config('app.stripe_webhook_secret');

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error("Stripe Webhook: Invalid payload", ['error' => $e->getMessage()]);
            return response("Invalid payload", 400);
        } catch (SignatureVerificationException $e) {
            Log::error("Stripe Webhook: Invalid signature", ['error' => $e->getMessage()]);
            return response("Invalid signature", 400);
        }

        switch ($event->type) {
            /**
             * Handle Stripe fee + commissions.
             */
            case 'charge.updated':
                $charge = $event->data->object;
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];

                $balanceTransaction = $stripe->balanceTransactions->retrieve($transactionId);

                $orders = Order::where('payment_intent', $paymentIntent)->get();

                $totalAmount = $balanceTransaction['amount'];
                $stripeFee = collect($balanceTransaction['fee_details'])
                    ->where('type', 'stripe_fee')
                    ->sum('amount');

                $platformFeePercent = config('app.platform_fee_pct');

                foreach ($orders as $order) {
                    $vendorShare = $order->total_price / $totalAmount;
                    $order->online_payment_commission = $vendorShare * $stripeFee;
                    $order->website_commission = ($order->total_price - $order->online_payment_commission) / 100 * $platformFeePercent;
                    $order->vendor_subtotal = $order->total_price - $order->online_payment_commission - $order->website_commission;
                    $order->save();
                }
                break;

            /**
             * Handle successful checkout completion.
             */
            case 'checkout.session.completed':
                $session = $event->data->object;
                $pi = $session['payment_intent'];

                $orders = Order::query()
                    ->with(['orderItems.product'])
                    ->where('stripe_session_id', $session['id'])
                    ->get();

                $allProductsToDelete = [];
                $userId = null;

                foreach ($orders as $order) {
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
                }

                // Remove purchased products from cart
                if ($userId && !empty($allProductsToDelete)) {
                    CartItem::query()
                        ->where('user_id', $userId)
                        ->whereIn('product_id', $allProductsToDelete)
                        ->where('saved_for_later', false)
                        ->delete();
                }
                break;

            default:
                Log::warning("Stripe Webhook: Received unknown event type", [
                    'type' => $event->type,
                ]);
        }

        return response('', 200);
    }
}
