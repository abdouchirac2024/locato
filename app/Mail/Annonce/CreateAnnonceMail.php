<?php

namespace App\Mail\Annonce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreateAnnonceMail extends Mailable
{
  
    use Queueable, SerializesModels;
    public $annonce;

    /**
     * Create a new message instance.
     */
    public function __construct($annonce)
    {
        //
        $this->annonce = $annonce;
    }


    public function build()
    {
        return $this->subject('Demande de creation d\'une annonce')
            ->view('/emails/Annonce/CreationAnnonce')
            ->with(['annonce' => $this->annonce]);
    }
}
