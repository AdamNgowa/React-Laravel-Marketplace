<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PayoutVendors extends Command
{
    protected $signature = 'payout:vendors';

    protected $description = 'Perform vendor payout';

    public function handle()
    {
        $this->info('Starting monthly payout process for vendors...');

        $vendors = Vendor::eligibleForPayout()->get();

        foreach ($vendors as $vendor) {
            $this->processPayout($vendor);
        }

        $this->info("Monthly payout process completed.");
        return Command::SUCCESS;
    }

    protected function processPayout(Vendor $vendor)
    {
        $this->info('Processing payout for vendor [ID='.$vendor->user_id.'] - "' . $vendor->store_name.'"');

        try {
            DB::beginTransaction();

            // Get last payout period
            $startingFrom = Payout::where('vendor_id', $vendor->user_id)
                ->orderBy('until', 'desc')
                ->value('until');

            $startingFrom = $startingFrom ?: Carbon::make('1970-01-01');

            // Cover up to the start of this month (previous full month)
            $until = Carbon::now()->startOfMonth();

            // Calculate vendor's subtotal since last payout
            $vendorSubtotal = Order::query()
                ->where('vendor_user_id', $vendor->user_id)
                ->where('status', OrderStatusEnum::Paid->value)
                ->whereBetween('created_at', [$startingFrom, $until])
                ->sum('vendor_subtotal');

            if ($vendorSubtotal > 0) {
                $this->info('Payout made with amount: ' . $vendorSubtotal);

                // Save payout record
                $payout = Payout::create([
                    'vendor_id'     => $vendor->user_id,
                    'amount'        => $vendorSubtotal,
                    'starting_from' => $startingFrom,
                    'until'         => $until,
                ]);

                // Perform Stripe transfer
                $transfer = $vendor->user->transferToStripeAccount(
                    (int) ($vendorSubtotal * 100), // convert to cents
                    config('app.currency', 'usd')
                );

                // Optional: store Stripe transfer ID (requires column in DB)
                if (isset($payout->stripe_transfer_id)) {
                    $payout->update(['stripe_transfer_id' => $transfer->id]);
                }

                $this->info("Stripe transfer created: " . $transfer->id);
            } else {
                $this->info('Nothing to process for this vendor');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Payout failed: " . $e->getMessage());
        }
    }
}
