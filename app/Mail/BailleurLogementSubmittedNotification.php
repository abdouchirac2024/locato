<?php
namespace App\Mail;

use App\Models\Logement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BailleurLogementSubmittedNotification extends Mailable implements ShouldQueue
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
            subject: 'Votre logement a été soumis pour validation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Vous pouvez passer d'autres variables à la vue si nécessaire
        // Par exemple, un lien vers le tableau de bord du bailleur
        return new Content(
            markdown: 'emails.bailleur.logement_submitted',
            with: [
                'logementUrl' => url('/mes-logements/' . $this->logement->id), // Adaptez cette URL
            ]
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
