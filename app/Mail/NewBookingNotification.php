<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * إيميل إشعار بحجز خدمة جديد (فورم «احجز فني») لصاحب الموقع.
 */
class NewBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        $service = $this->booking->service ? ' — ' . $this->booking->service : '';

        return new Envelope(
            subject: 'حجز خدمة جديد' . $service . ' — ' . site('name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-booking',
            with: ['booking' => $this->booking],
        );
    }
}
