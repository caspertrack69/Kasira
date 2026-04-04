<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\View\View;

class PublicInvoiceController extends Controller
{
    public function show(string $token): View
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer', 'items'])
            ->where('public_token', $token)
            ->firstOrFail();

        return view('invoices.public', ['invoice' => $invoice]);
    }
}
