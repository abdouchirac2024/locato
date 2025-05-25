<?php

namespace App\Listeners;

use App\Events\VisitCompleted;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue; // Optional: if you want to queue this
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateVisitInvoiceListener // implements ShouldQueue // Optional
{
    use InteractsWithQueue; // Optional

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function handle(VisitCompleted $event): void
    {
        try {
            // Assume recipient is the Locataire of the visit.
            // Assume visit itself is free, so amount is 0 unless other logic dictates.
            $locataireUser = $event->visite->locataire->user;
            if ($locataireUser) {
                $this->invoiceService->generateVisitInvoice(
                    $event->visite,
                    $locataireUser,
                    0.00, // Amount, assuming 0 for now
                    'XOF', // Default currency
                    'Facture pour visite effectuée.'
                );
                Log::info("Invoice generated for completed visit: { $event->visite->id }");
            } else {
                Log::warning("GenerateVisitInvoiceListener: Locataire user not found for visit: { $event->visite->id }");
            }
        } catch (\Exception $e) {
            Log::error("GenerateVisitInvoiceListener: Failed to generate invoice for visit { $event->visite->id }. Error: { $e->getMessage() }");
        }
    }
}
