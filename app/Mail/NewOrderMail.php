<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Only store the order ID, not the whole model.
     */
    public function __construct(public int $orderId) {}

    /**
     * Define envelope (subject, etc.)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order Received',
        );
    }

    /**
     * Define email content and pass a fresh Order model.
     */
    public function content(): Content
    {
        $order = Order::with(['orderItems.product', 'vendorUser.vendor'])
            ->findOrFail($this->orderId);

        return new Content(
            view: 'mail.new_order',
            with: [
                'order' => $order,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
