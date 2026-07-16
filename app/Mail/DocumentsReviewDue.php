<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentsReviewDue extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Collection $documents) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->documents->count()." dokumen jatuh tempo tinjau ulang — PUSAKA HUKUM",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents-review-due',
        );
    }
}
