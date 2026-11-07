<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountCreated extends Mailable
{
    public function __construct(
        public string $name,
        public string $email,
        public string $generatedPassword,
        public string $role,
        public string $changePasswordUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your PPMMIS Account Login Details');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-created');
    }
}
