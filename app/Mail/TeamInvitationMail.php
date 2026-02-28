<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->invitation->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-invitation',
            with: [
                'acceptUrl' => route('invitations.accept', $this->invitation->token),
                'tenantName' => $this->invitation->tenant->name,
                'inviterName' => $this->invitation->inviter->name,
                'role' => ucfirst($this->invitation->role),
                'expiresAt' => $this->invitation->expires_at->format('F j, Y'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
