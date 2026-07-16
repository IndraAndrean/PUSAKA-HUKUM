<?php

namespace App\Mail;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultationAnswered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Consultation $consultation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jawaban Konsultasi Anda — '.$this->consultation->tracking_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.consultation-answered',
        );
    }
}
