<?php

namespace App\Mail;

use App\Models\VendorProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public VendorProfile $vendor,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Qong Systems Vendor Application — Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-rejection',
        );
    }
}
