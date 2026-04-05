<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentAllocationService;
use App\Services\Payment\OnlinePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class PublicInvoicePaymentController extends Controller
{
    public function __construct(
        private readonly OnlinePaymentService $onlinePaymentService,
        private readonly PaymentAllocationService $allocationService,
    ) {
    }

    public function store(string $token): RedirectResponse
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer'])
            ->where('public_token', $token)
            ->firstOrFail();

        $payment = $this->onlinePaymentService->startQrisPayment($invoice);
        $paymentData = $this->onlinePaymentService->paymentData($payment);

        return redirect()
            ->route('invoices.public.show', ['token' => $token])
            ->with('status', $paymentData['qr_string'] ? 'QRIS payment is ready.' : 'Online payment checkout has been created.');
    }

    public function storeBankTransfer(Request $request, string $token): RedirectResponse
    {
        $invoice = Invoice::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer'])
            ->where('public_token', $token)
            ->firstOrFail();

        abort_if(! in_array($invoice->status, ['sent', 'partial', 'overdue'], true), 422, 'Invoice is not payable.');
        abort_if((float) $invoice->amount_due <= 0, 422, 'Invoice no longer has an outstanding balance.');

        $validated = $request->validate([
            'payment_method_id' => [
                'required',
                'string',
                Rule::exists('payment_methods', 'id')->where(fn ($query) => $query
                    ->where('entity_id', $invoice->entity_id)
                    ->where('type', 'bank_transfer')
                    ->where('is_active', true)),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'lte:'.$invoice->amount_due],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'private');

        try {
            DB::transaction(function () use ($invoice, $validated, $proofPath): void {
                $payment = Payment::query()->create([
                    'entity_id' => $invoice->entity_id,
                    'customer_id' => $invoice->customer_id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'payment_number' => sprintf('PAY-MAN-%s-%s', now()->format('YmdHis'), strtoupper(Str::random(6))),
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'reference' => $validated['reference'] ?? null,
                    'status' => PaymentStatus::Pending->value,
                    'notes' => $validated['notes'] ?? null,
                    'proof_path' => $proofPath,
                    'created_by' => $invoice->created_by,
                ]);

                $this->allocationService->allocate($payment, [
                    $invoice->getKey() => $validated['amount'],
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($proofPath);

            throw $exception;
        }

        return redirect()
            ->route('invoices.public.show', ['token' => $token])
            ->with('status', 'Transfer proof submitted. We will verify your payment shortly.');
    }
}
