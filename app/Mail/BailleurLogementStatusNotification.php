<?php
namespace App\Mail;

use App\Models\Logement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BailleurLogementStatusNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Logement $logement;
    public string $newStatus; // 'approuve' ou 'rejete'
    public ?string $adminNotes;

    /**
     * Create a new message instance.
     */
    public function __construct(Logement $logement, string $newStatus, ?string $adminNotes = null)
    {
        $this->logement = $logement;
        $this->newStatus = $newStatus;
        $this->adminNotes = $adminNotes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
         $subject = $this->newStatus === Logement::STATUS_APPROUVE
                    ? 'Votre Logement a été Approuvé !'
                    : 'Mise à jour concernant votre Logement';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bailleur.logement_status',
             with: [
                 'statusFrancais' => $this->newStatus === Logement::STATUS_APPROUVE ? 'Approuvé' : 'Rejeté',
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
