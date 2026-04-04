<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CreditNote::class);

        return view('credit-notes.index', [
            'creditNotes' => CreditNote::query()->with('invoice')->latest()->paginate(20),
            'invoices' => Invoice::query()->whereIn('status', ['partial', 'paid'])->latest('invoice_date')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CreditNote::class);

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string'],
        ]);

        $invoice = Invoice::query()->findOrFail($validated['invoice_id']);
        $prefix = strtoupper(substr($invoice->entity->invoice_prefix ?? 'ENT', 0, 6));

        CreditNote::query()->create([
            'entity_id' => $invoice->entity_id,
            'invoice_id' => $invoice->getKey(),
            'customer_id' => $invoice->customer_id,
            'credit_note_number' => 'CN-'.$prefix.'-'.now()->format('Y').'-'.Str::padLeft((string) random_int(1, 9999), 4, '0'),
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'status' => 'issued',
            'created_by' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Credit note issued.');
    }
}
