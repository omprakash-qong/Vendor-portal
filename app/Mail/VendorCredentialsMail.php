<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Qong Systems Vendor Portal Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-credentials',
        );
    }
}
