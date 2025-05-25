<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVisiteRequestForAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $visite;

    /**
     * Create a new notification instance.
     */
    public function __construct($visite)
    {
        $this->visite = $visite;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $visite = $this->visite;
        $logement = $visite->logement;
        $locataireUser = $visite->locataire?->user; // Accéder à l'utilisateur via la relation locataire
        $bailleurUser = $logement?->bailleur?->user; // Accéder à l'utilisateur du bailleur via le logement

        $mailMessage = (new MailMessage)
                    ->subject('Nouvelle demande de visite pour le logement #' . $logement->id)
                    ->line('Une nouvelle demande de visite a été soumise pour le logement suivant :');

        if ($logement) {
            $mailMessage->line('**Détails du Logement :**');
            $mailMessage->line('ID : ' . $logement->id);
            $mailMessage->line('Titre : ' . $logement->titre);
            // Ajoutez d'autres détails du logement si nécessaire
            $mailMessage->line('Adresse : ' . $logement->adresse . ', ' . ($logement->quartier?->nom ?? 'N/A') . ', ' . ($logement->quartier?->ville?->nom ?? 'N/A'));
        }

        if ($locataireUser) {
            $mailMessage->line('**Détails du Locataire :**');
            $mailMessage->line('Nom : ' . $locataireUser->name);
            $mailMessage->line('Email : ' . $locataireUser->email);
            // Ajoutez d'autres coordonnées du locataire si disponibles
        }
         // Assurez-vous que la relation bailleur est chargée sur le logement si nécessaire
        if ($bailleurUser) {
            $mailMessage->line('**Détails du Bailleur :**');
            $mailMessage->line('Nom : ' . $bailleurUser->name);
            $mailMessage->line('Email : ' . $bailleurUser->email);
            // Ajoutez d'autres coordonnées du bailleur si disponibles
        }

        $mailMessage->line('**Détails de la Demande de Visite :**');
        $mailMessage->line('Date Souhaitée : ' . $visite->dateVisite?->format('d/m/Y'));
        $mailMessage->line('Heure Souhaitée : ' . ($visite->heureVisite ? \Carbon\Carbon::parse($visite->heureVisite)->format('H:i') : 'N/A'));
        $mailMessage->line('Statut : ' . $visite->statut);
        // Ajoutez d'autres détails de la visite si nécessaire


        $mailMessage->action('Voir la demande de visite', url('/admin/visites/' . $visite->id)); // Exemple d'URL admin

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
