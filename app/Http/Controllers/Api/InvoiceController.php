<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse; // For download response
use Illuminate\Http\JsonResponse; // For error responses

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Allows an authenticated user to download their own invoice.
     *
     * @param Invoice $invoice The invoice model instance (route model binding).
     * @return StreamedResponse|JsonResponse
     */
    public function download(Invoice $invoice): StreamedResponse|JsonResponse
    {
        // Authorize: User must be the owner of the invoice or an admin
        if (Auth::id() !== $invoice->user_id && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized to download this invoice.'], 403);
        }

        if (!$invoice->pdf_path || !Storage::disk('local')->exists($invoice->pdf_path)) {
            return response()->json(['message' => 'Invoice PDF not found.'], 404);
        }

        // Use Storage::download to stream the file
        // The third argument is optional for setting the download filename,
        // by default it uses the basename of $invoice->pdf_path.
        return Storage::disk('local')->download($invoice->pdf_path);
    }
}
