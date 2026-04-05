<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\Billing\CreditNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNoteService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', CreditNote::class);

        return view('credit-notes.index', [
            'creditNotes' => CreditNote::query()->with(['invoice', 'customer'])->latest()->paginate(20),
            'invoices' => Invoice::query()->with('customer')->whereIn('status', ['partial', 'paid'])->latest('invoice_date')->get(),
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
        $this->creditNoteService->issue(
            $invoice,
            (string) $validated['amount'],
            $validated['reason'],
            $request->user()->getKey(),
        );

        return back()->with('status', 'Credit note issued and applied.');
    }
}
