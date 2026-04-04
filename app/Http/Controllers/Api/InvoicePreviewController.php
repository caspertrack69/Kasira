<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Billing\InvoiceCalculationService;
use App\Support\EntityContext;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoicePreviewController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
    ) {
    }

    public function __invoke(Request $request, EntityContext $context): JsonResponse
    {
        abort_unless($request->user()?->can('invoices.manage'), 403);

        $entity = $context->entity();
        abort_if(! $entity, 422, 'No active entity selected.');

        $validated = $request->validate([
            'invoice_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_id' => ['nullable', 'exists:taxes,id'],
        ]);

        $totals = $this->calculationService->calculate($validated['items']);
        $invoiceDate = new DateTimeImmutable((string) $validated['invoice_date']);

        return response()->json([
            'success' => true,
            'data' => [
                ...$totals,
                'due_date' => $invoiceDate->modify('+'.$entity->default_payment_terms.' days')->format('Y-m-d'),
                'currency' => $entity->currency,
            ],
            'message' => 'Invoice preview generated.',
            'errors' => null,
        ]);
    }
}
