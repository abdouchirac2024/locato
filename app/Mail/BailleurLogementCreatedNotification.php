<?php
namespace App\Mail;

use App\Models\Logement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BailleurLogementCreatedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Logement $logement;

    /**
     * Create a new message instance.
     */
    public function __construct(Logement $logement)
    {
        $this->logement = $logement;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Un nouveau logement a été ajouté à votre compte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bailleur.logement_created_by_admin', // Vue spécifique
            with: [
                'logementUrl' => url('/'), // Mettre un lien vers le front-end approprié
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
