<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * إيميل إشعار بطلب جديد لصاحب الموقع — المرحلة 6C.
 */
class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'طلب جديد #' . $this->order->order_number . ' — ' . site('name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order',
            with: ['order' => $this->order->loadMissing('items')],
        );
    }
}
