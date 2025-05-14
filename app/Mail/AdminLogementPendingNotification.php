<?php
namespace App\Mail;

use App\Models\Logement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLogementPendingNotification extends Mailable implements ShouldQueue // Implemente ShouldQueue
{
    use Queueable, SerializesModels;

    public Logement $logement; // Logement en attente
    // Passer aussi le bailleur pour accès facile dans l'email
    public $bailleurUser;

    /**
     * Create a new message instance.
     */
    public function __construct(Logement $logement)
    {
        $this->logement = $logement;
        // Assurez-vous que la relation est chargée avant de passer
        $this->bailleurUser = $logement->bailleur?->user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau Logement en Attente de Validation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.logement_pending', // Vue Markdown
            with: [
                'logementUrl' => url('/admin/logements/' . $this->logement->id), // Exemple URL admin
                'bailleurName' => $this->bailleurUser?->name . ' ' . $this->bailleurUser?->prenom,
                'bailleurEmail' => $this->bailleurUser?->email,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
