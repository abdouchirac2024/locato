<?php

namespace App\Listeners;

use App\Events\PaymentMade;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue; // Optional
use Illuminate\Queue\InteractsWithQueue;   // Optional
use Illuminate\Support\Facades\Log;

class GenerateTransactionInvoiceListener // implements ShouldQueue // Optional
{
    use InteractsWithQueue; // Optional

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function handle(PaymentMade $event): void
    {
        try {
            // The description for the transaction invoice can be generic for now,
            // or enhanced if Payement model gets more details (e.g., a 'description' or 'type' field)
            $this->invoiceService->generateTransactionInvoice(
                $event->payment,
                "Facture relative au paiement ID: { $event->payment->id }"
            );
            Log::info("Invoice generated for payment: { $event->payment->id }");
        } catch (\Exception $e) {
            Log::error("GenerateTransactionInvoiceListener: Failed to generate invoice for payment { $event->payment->id }. Error: { $e->getMessage() }");
        }
    }
}
