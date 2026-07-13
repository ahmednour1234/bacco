<?php

namespace App\Mail;

use App\Models\QuotationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationPricedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuotationRequest $quotation,
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم تسعير عرض السعر الخاص بك - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation-priced',
        );
    }
}
