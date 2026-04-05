<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatusHistory;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Billing\InvoiceCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
    ) {
    }

    /**
     * Run the demo invoice bootstrap.
     */
    public function run(): void
    {
        $entities = Entity::query()->orderBy('name')->get();
        if ($entities->isEmpty()) {
            return;
        }

        $fallbackCreator = User::query()->orderBy('id')->first();
        if (! $fallbackCreator) {
            return;
        }

        foreach ($entities as $entity) {
            $customers = Customer::query()
                ->withoutGlobalScopes()
                ->where('entity_id', $entity->getKey())
                ->orderBy('name')
                ->get();

            $items = Item::query()
                ->withoutGlobalScopes()
                ->where('entity_id', $entity->getKey())
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            if ($customers->isEmpty() || $items->isEmpty()) {
                continue;
            }

            $invoicePrefix = $entity->invoice_prefix ?: 'INV';
            $creatorId = $fallbackCreator->getKey();
            $sequence = 1;

            for ($monthOffset = 0; $monthOffset < 6; $monthOffset++) {
                foreach ($customers as $customerIndex => $customer) {
                    $status = $this->resolveStatus($monthOffset, $customerIndex);
                    $invoiceDate = now()->subMonthsNoOverflow($monthOffset)->startOfMonth()->addDays(2 + $customerIndex);
                    $dueDate = (clone $invoiceDate)->addDays(14);

                    if ($status === 'overdue') {
                        $invoiceDate = now()->subMonthsNoOverflow(max($monthOffset, 1))->startOfMonth()->addDays(1);
                        $dueDate = now()->subDays(8)->startOfDay();
                    }

                    $invoiceNumber = sprintf(
                        '%s-%s-%s',
                        $invoicePrefix,
                        $invoiceDate->format('Ym'),
                        str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    );
                    $sequence++;

                    $lineItems = $this->buildLineItems($items, $monthOffset, $customerIndex);
                    $totals = $this->calculationService->calculate($lineItems);
                    $grandTotal = (string) $totals['grand_total'];
                    $partialPaid = $this->partialAmountPaid($grandTotal);

                    [$amountPaid, $amountDue] = match ($status) {
                        'paid' => [$grandTotal, '0.00'],
                        'partial' => [$partialPaid, bcsub($grandTotal, $partialPaid, 2)],
                        default => ['0.00', $grandTotal],
                    };

                    $this->seedInvoice(
                        entity: $entity,
                        customer: $customer,
                        creatorId: $creatorId,
                        status: $status,
                        invoiceNumber: $invoiceNumber,
                        invoiceDate: $invoiceDate->toDateString(),
                        dueDate: $dueDate->toDateString(),
                        totals: $totals,
                        amountPaid: $amountPaid,
                        amountDue: $amountDue,
                    );
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLineItems(\Illuminate\Support\Collection $items, int $monthOffset, int $customerIndex): array
    {
        return $items
            ->shuffle()
            ->take(min(2 + (($monthOffset + $customerIndex) % 2), $items->count()))
            ->values()
            ->map(function (Item $item, int $itemIndex) use ($monthOffset): array {
                $quantity = (string) max(1, ($itemIndex + 1 + ($monthOffset % 2)));
                $discountType = $itemIndex === 0 ? 'percentage' : null;
                $discountValue = $itemIndex === 0 ? (string) (3 + ($monthOffset % 5)) : null;

                return [
                    'description' => $item->name,
                    'item_id' => $item->getKey(),
                    'quantity' => $quantity,
                    'unit_price' => (string) $item->default_price,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_id' => $item->tax_id,
                ];
            })
            ->all();
    }

    private function resolveStatus(int $monthOffset, int $customerIndex): string
    {
        $pattern = ['sent', 'paid', 'partial', 'sent', 'overdue', 'draft'];

        return $pattern[($monthOffset + $customerIndex) % count($pattern)];
    }

    /**
     * @param array{subtotal:string,discount_total:string,tax_total:string,grand_total:string,items:array<int,array<string,mixed>>} $totals
     */
    private function seedInvoice(
        Entity $entity,
        Customer $customer,
        int $creatorId,
        string $status,
        string $invoiceNumber,
        string $invoiceDate,
        string $dueDate,
        array $totals,
        string $amountPaid,
        string $amountDue,
    ): void {
        DB::transaction(function () use (
            $entity,
            $customer,
            $creatorId,
            $status,
            $invoiceNumber,
            $invoiceDate,
            $dueDate,
            $totals,
            $amountPaid,
            $amountDue
        ): void {
            $invoice = Invoice::query()->withoutGlobalScopes()->firstOrNew([
                'entity_id' => $entity->getKey(),
                'invoice_number' => $invoiceNumber,
            ]);

            if (! $invoice->exists) {
                $invoice->public_token = (string) Str::ulid();
                $invoice->created_by = $creatorId;
            }

            $invoice->fill([
                'customer_id' => $customer->getKey(),
                'status' => $status,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'amount_paid' => $amountPaid,
                'amount_due' => $amountDue,
                'currency' => $entity->currency ?: 'IDR',
                'notes' => 'Demo seeded invoice for UI and workflow testing.',
                'terms' => 'Payment due within 14 days from invoice date.',
                'sent_at' => in_array($status, ['sent', 'partial', 'paid', 'overdue'], true) ? now()->subDays(2) : null,
                'paid_at' => $status === 'paid' ? now()->subDay() : null,
            ])->save();

            InvoiceItem::query()->withoutGlobalScopes()->where('invoice_id', $invoice->getKey())->delete();

            foreach ($totals['items'] as $item) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->getKey(),
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'tax_id' => $item['tax_id'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $item['subtotal'],
                    'sort_order' => $item['sort_order'],
                ]);
            }

            InvoiceStatusHistory::query()->updateOrCreate(
                [
                    'entity_id' => $entity->getKey(),
                    'invoice_id' => $invoice->getKey(),
                    'to_status' => $status,
                ],
                [
                    'from_status' => null,
                    'changed_by' => $creatorId,
                    'notes' => 'Seeded initial status.',
                    'created_at' => now()->subDay(),
                ],
            );

            $this->seedPayment($invoice, $entity, $amountPaid, $creatorId);
        });
    }

    private function partialAmountPaid(string $grandTotal): string
    {
        $raw = bcmul($grandTotal, '0.45', 2);

        return bccomp($raw, '0.00', 2) === 1 ? $raw : '0.00';
    }

    private function seedPayment(Invoice $invoice, Entity $entity, string $amountPaid, int $creatorId): void
    {
        if (bccomp($amountPaid, '0.00', 2) !== 1) {
            return;
        }

        $paymentMethod = PaymentMethod::query()
            ->withoutGlobalScopes()
            ->where('entity_id', $entity->getKey())
            ->where('is_active', true)
            ->where('type', 'bank_transfer')
            ->latest('updated_at')
            ->first();

        if (! $paymentMethod) {
            return;
        }

        $payment = Payment::query()->create([
            'entity_id' => $entity->getKey(),
            'customer_id' => $invoice->customer_id,
            'payment_method_id' => $paymentMethod->getKey(),
            'payment_number' => sprintf('PAY-SEED-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(4))),
            'amount' => $amountPaid,
            'payment_date' => $invoice->invoice_date,
            'reference' => 'Seeded payment',
            'status' => PaymentStatus::Confirmed->value,
            'notes' => 'Auto-generated payment for dashboard seed data.',
            'created_by' => $creatorId,
        ]);

        PaymentAllocation::query()->create([
            'payment_id' => $payment->getKey(),
            'invoice_id' => $invoice->getKey(),
            'amount' => $amountPaid,
        ]);
    }
}
