<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class HostVisitorCheckinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Visit $visit)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Visitor Check-in Notification - ' . $this->visit->visitor->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.host-visitor-checkin',
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        // Attach visitor photo if exists
        if ($this->visit->photo && \Storage::disk('public')->exists($this->visit->photo)) {
            $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->visit->photo))
                ->as('visitor-photo.jpg')
                ->withMime('image/jpeg');
        }
        
        return $attachments;
    }
}
