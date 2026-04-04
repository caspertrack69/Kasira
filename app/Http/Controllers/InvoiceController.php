<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use App\Models\Tax;
use App\Services\Billing\InvoiceCalculationService;
use App\Services\Billing\InvoiceNumberService;
use App\Support\EntityContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly InvoiceNumberService $numberService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with('customer')->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->string('customer_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($inner) use ($search): void {
                $inner->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return view('invoices.index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'taxes' => Tax::query()->orderBy('name')->get(),
            'statuses' => array_map(static fn (InvoiceStatus $case): string => $case->value, InvoiceStatus::cases()),
        ]);
    }

    public function store(Request $request, EntityContext $context): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $entity = $context->entity();
        abort_if(! $entity, 422, 'No active entity selected.');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_id' => ['nullable', 'exists:taxes,id'],
        ]);

        $totals = $this->calculationService->calculate($validated['items']);
        $entityModel = Entity::query()->findOrFail($entity->getKey());

        DB::transaction(function () use ($validated, $entityModel, $totals): void {
            $invoiceDate = new \DateTimeImmutable((string) $validated['invoice_date']);

            $invoice = Invoice::query()->create([
                'entity_id' => $entityModel->getKey(),
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $this->numberService->generate($entityModel, $invoiceDate),
                'status' => InvoiceStatus::Draft->value,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? $invoiceDate->modify('+'.$entityModel->default_payment_terms.' days')->format('Y-m-d'),
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'amount_paid' => '0.00',
                'amount_due' => $totals['grand_total'],
                'currency' => strtoupper($validated['currency'] ?? $entityModel->currency),
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'public_token' => (string) Str::ulid().Str::random(10),
                'created_by' => request()->user()->getKey(),
            ]);

            $invoice->items()->createMany($totals['items']);

            InvoiceStatusHistory::query()->create([
                'entity_id' => $invoice->entity_id,
                'invoice_id' => $invoice->getKey(),
                'to_status' => $invoice->status,
                'changed_by' => request()->user()->getKey(),
                'notes' => 'Invoice created as draft',
            ]);

            GenerateInvoicePdfJob::dispatch($invoice->getKey());
        });

        return back()->with('status', 'Invoice drafted successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['entity', 'customer', 'items.tax', 'allocations.payment', 'statusHistories']);

        return view('invoices.show', ['invoice' => $invoice]);
    }

    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $copy = $invoice->replicate([
            'invoice_number',
            'status',
            'sent_at',
            'paid_at',
            'cancelled_at',
            'pdf_path',
            'public_token',
            'amount_paid',
        ]);

        $entity = Entity::query()->findOrFail($invoice->entity_id);
        $copy->invoice_number = $this->numberService->generate($entity, new \DateTimeImmutable());
        $copy->status = InvoiceStatus::Draft->value;
        $copy->public_token = (string) Str::ulid().Str::random(10);
        $copy->amount_paid = '0.00';
        $copy->amount_due = $copy->grand_total;
        $copy->created_by = request()->user()->getKey();
        $copy->save();

        foreach ($invoice->items as $item) {
            $copy->items()->create($item->only([
                'item_id',
                'description',
                'quantity',
                'unit_price',
                'discount_type',
                'discount_value',
                'discount_amount',
                'tax_id',
                'tax_rate',
                'tax_amount',
                'subtotal',
                'sort_order',
            ]));
        }

        return redirect()->route('invoices.show', $copy)->with('status', 'Invoice duplicated as draft.');
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);

        $fromStatus = $invoice->status;

        $invoice->update([
            'status' => InvoiceStatus::Void->value,
            'cancelled_at' => now(),
        ]);

        InvoiceStatusHistory::query()->create([
            'entity_id' => $invoice->entity_id,
            'invoice_id' => $invoice->getKey(),
            'from_status' => $fromStatus,
            'to_status' => InvoiceStatus::Void->value,
            'changed_by' => request()->user()->getKey(),
            'notes' => 'Invoice voided by privileged user',
        ]);

        return back()->with('status', 'Invoice voided.');
    }
}
