<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\StripeClient;

class ProcessChargeUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $charge) {}

    public function handle(): void
    {
        $stripe = new StripeClient(config('app.stripe_secret_key'));
        $transactionId = $this->charge['balance_transaction'];
        $paymentIntent = $this->charge['payment_intent'];

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
            $order->website_commission = ($order->total_price - $order->online_payment_commission) * ($platformFeePercent / 100);
            $order->vendor_subtotal = $order->total_price - $order->online_payment_commission - $order->website_commission;
            $order->save();
        }
    }
}
