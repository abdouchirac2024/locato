<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Visite;
use App\Models\Payement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class InvoiceService
{
    private function generateInvoiceNumber(): string
    {
        $dateComponent = Carbon::now()->format('Ymd');
        $randomComponent = strtoupper(Str::random(5));
        $nextId = (Invoice::orderBy('id', 'desc')->first()?->id ?? 0) + 1;
        return "INV-{$dateComponent}-{$nextId}-{$randomComponent}";
    }

    public function generateVisitInvoice(Visite $visite, User $invoiceRecipient, float $amount = 0.0, string $currency = 'XOF', string $notes = ''): Invoice
    {
        $logement = $visite->logement;
        $bailleur = $logement->bailleur->user;
        $locataire = $visite->locataire->user;

        $invoiceData = [
            'visit_details' => $visite->toArray(),
            'logement_details' => $logement->toArray(),
            'bailleur_name' => $bailleur->name . ' ' . $bailleur->prenom,
            'locataire_name' => $locataire->name . ' ' . $locataire->prenom,
            'invoice_recipient_name' => $invoiceRecipient->name . ' ' . $invoiceRecipient->prenom,
            'invoice_recipient_email' => $invoiceRecipient->email,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => Carbon::now(),
            'due_date' => Carbon::now(),
            'total_amount' => $amount,
            'currency' => $currency,
            'items' => [
                ['description' => "Frais de visite pour le logement: " . ($logement->libelle_display ?? $logement->libelle), 'quantity' => 1, 'unit_price' => $amount, 'total' => $amount]
            ],
            'notes' => $notes ?: "Facture pour la visite du logement " . ($logement->reference ?? '') . " le " . ($visite->dateVisite ? $visite->dateVisite->format('d/m/Y') : 'N/A') . ".",
        ];

        $htmlContent = "<h1>Facture N°: {$invoiceData['invoice_number']}</h1>" .
                       "<p>Date: {$invoiceData['invoice_date']->format('d/m/Y')}</p>" .
                       "<p>Montant: {number_format($invoiceData['total_amount'], 2)} {$invoiceData['currency']}</p>" .
                       "<p>Pour: {$invoiceData['invoice_recipient_name']}</p>" .
                       "<p>Visite du logement: {($logement->libelle_display ?? $logement->libelle)}</p>";
        
        $pdf = Pdf::loadHTML($htmlContent);
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $directoryForStorage = "invoices/{$year}/{$month}";
        $filename = $invoiceData['invoice_number'] . '.pdf';
        
        Storage::disk('local')->makeDirectory($directoryForStorage);
        Storage::disk('local')->put($directoryForStorage . '/' . $filename, $pdf->output());
        
        $dbFilePath = $directoryForStorage . '/' . $filename;

        $invoice = Invoice::create([
            'user_id' => $invoiceRecipient->id,
            'related_model_type' => Visite::class,
            'related_model_id' => $visite->id,
            'invoice_number' => $invoiceData['invoice_number'],
            'invoice_date' => $invoiceData['invoice_date'],
            'due_date' => $invoiceData['due_date'],
            'total_amount' => $invoiceData['total_amount'],
            'currency' => $invoiceData['currency'],
            'status' => $amount > 0 ? 'draft' : 'paid',
            'pdf_path' => $dbFilePath,
            'notes' => $invoiceData['notes'],
        ]);

        return $invoice;
    }

    public function generateTransactionInvoice(Payement $payement, string $description = "Prestation de services"): Invoice
    {
        $user = $payement->locataire->user;

        $invoiceData = [
            'payment_details' => $payement->toArray(),
            'recipient_name' => $user->name . ' ' . $user->prenom,
            'recipient_email' => $user->email,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => Carbon::now(),
            'due_date' => Carbon::now(),
            'total_amount' => $payement->montant,
            'currency' => $payement->currency ?? 'XOF',
            'items' => [
                ['description' => $description, 'quantity' => 1, 'unit_price' => $payement->montant, 'total' => $payement->montant]
            ],
            'notes' => "Facture pour le paiement référence {$payement->reference}"
        ];

        $htmlContent = "<h1>Facture N°: {$invoiceData['invoice_number']}</h1>" .
                   "<p>Date: {$invoiceData['invoice_date']->format('d/m/Y')}</p>" .
                   "<p>Montant: {number_format($invoiceData['total_amount'], 2)} {$invoiceData['currency']}</p>" .
                   "<p>Pour: {$invoiceData['recipient_name']}</p>" .
                   "<p>Raison: {$description}</p>";

        $pdf = Pdf::loadHTML($htmlContent);
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $directoryForStorage = "invoices/{$year}/{$month}";
        $filename = $invoiceData['invoice_number'] . '.pdf';

        Storage::disk('local')->makeDirectory($directoryForStorage);
        Storage::disk('local')->put($directoryForStorage . '/' . $filename, $pdf->output());
        
        $dbFilePath = $directoryForStorage . '/' . $filename;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'related_model_type' => Payement::class,
            'related_model_id' => $payement->id,
            'invoice_number' => $invoiceData['invoice_number'],
            'invoice_date' => $invoiceData['invoice_date'],
            'due_date' => $invoiceData['due_date'],
            'total_amount' => $invoiceData['total_amount'],
            'currency' => $invoiceData['currency'],
            'status' => 'paid',
            'pdf_path' => $dbFilePath,
            'notes' => $invoiceData['notes'],
        ]);

        return $invoice;
    }
}
?>
