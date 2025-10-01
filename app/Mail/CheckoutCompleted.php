<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public array $orderIds;

    /**
     * Constructor: store order IDs safely.
     */
    public function __construct(array $orderIds)
    {
        $this->orderIds = array_filter($orderIds, 'is_numeric'); // only valid numeric IDs
    }

    /**
     * Email envelope (subject).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank you for your purchase',
        );
    }

    /**
     * Email content: pass fresh orders to Blade view.
     */
    public function content(): Content
    {
        $orders = Order::with(['orderItems.product', 'vendorUser.vendor'])
            ->whereIn('id', $this->orderIds)
            ->get();

        return new Content(
            view: 'mail.checkout_completed',
            with: [
                'orders' => $orders,
            ]
        );
    }

    /**
     * No attachments.
     */
    public function attachments(): array
    {
        return [];
    }
}
