<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Mail\CheckoutCompleted;
use App\Mail\NewOrderMail;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;


class StripeController extends Controller
{
    /**
     * Stripe checkout success page.
     */
    public function success(Request $request)
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');
        $orders = Order::where('stripe_session_id', $session_id)->get();

        if ($orders->count() === 0) {
            abort(404);
        }

        foreach ($orders as $order) {
            if ($order->user_id !== $user->id) {
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
    $endpointSecret = config('app.stripe_webhook_secret');
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            $endpointSecret
        );
    } catch (\Exception $e) {
        Log::error("Stripe Webhook error", ['error' => $e->getMessage()]);
        return response("Webhook error", 400);
    }

    switch ($event->type) {
        case 'checkout.session.completed':
            dispatch(new \App\Jobs\ProcessCheckoutSession($event->data->object->toArray()));
            break;

        case 'charge.updated':
            dispatch(new \App\Jobs\ProcessChargeUpdated($event->data->object->toArray()));
            break;

        default:
            Log::warning("Stripe Webhook: Unknown event", [
                'type' => $event->type,
            ]);
    }

    //  Always return fast
    return response()->json(['status' => 'ok'], 200);
}


}
