<?php

namespace App\Http\Controllers;

use App\Jobs\SendInvoiceEmailJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentAllocationService;
use App\Services\Payment\PaymentConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentAllocationService $allocationService,
        private readonly PaymentConfirmationService $confirmationService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Payment::class);

        return view('payments.index', [
            'payments' => Payment::query()->with(['customer', 'paymentMethod'])->latest('payment_date')->paginate(20),
            'customers' => Customer::query()->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::query()->orderBy('name')->get(),
            'invoices' => Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])->orderBy('invoice_date', 'desc')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_number' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['nullable', 'array'],
            'allocations.*' => ['nullable', 'numeric', 'min:0'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'private');
        }

        $payment = Payment::query()->create([
            'customer_id' => $validated['customer_id'] ?? null,
            'payment_method_id' => $validated['payment_method_id'],
            'payment_number' => $validated['payment_number'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'proof_path' => $proofPath,
            'status' => 'pending',
            'created_by' => $request->user()->getKey(),
        ]);

        $this->allocationService->allocate($payment, $validated['allocations'] ?? []);

        return back()->with('status', 'Payment recorded as pending confirmation.');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['customer', 'paymentMethod', 'allocations.invoice']);

        return view('payments.show', ['payment' => $payment]);
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('confirm', $payment);

        $this->confirmationService->confirm($payment, $request->user()->getKey());

        return back()->with('status', 'Payment confirmed.');
    }
}
