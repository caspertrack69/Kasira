<?php

namespace App\Services\Billing;

use App\Models\Tax;

class InvoiceCalculationService
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{subtotal:string,discount_total:string,tax_total:string,grand_total:string,items:array<int,array<string,mixed>>}
     */
    public function calculate(array $items): array
    {
        $subtotal = '0';
        $discountTotal = '0';
        $taxTotal = '0';
        $normalizedItems = [];

        foreach ($items as $index => $item) {
            $quantity = (string) ($item['quantity'] ?? '0');
            $unitPrice = (string) ($item['unit_price'] ?? '0');
            $lineBase = bcmul($quantity, $unitPrice, 2);

            $discountType = $item['discount_type'] ?? null;
            $discountValue = (string) ($item['discount_value'] ?? '0');
            $discountAmount = '0';

            if ($discountType === 'percentage') {
                $discountAmount = bcdiv(bcmul($lineBase, $discountValue, 4), '100', 2);
            } elseif ($discountType === 'fixed') {
                $discountAmount = $discountValue;
            }

            if (bccomp($discountAmount, $lineBase, 2) === 1) {
                $discountAmount = $lineBase;
            }

            $lineAfterDiscount = bcsub($lineBase, $discountAmount, 2);

            $taxRate = '0';
            if (! empty($item['tax_id'])) {
                /** @var Tax|null $tax */
                $tax = Tax::query()->find($item['tax_id']);
                $taxRate = (string) ($tax?->rate ?? '0');
            }

            $taxAmount = bcdiv(bcmul($lineAfterDiscount, $taxRate, 4), '100', 2);
            $lineTotal = bcadd($lineAfterDiscount, $taxAmount, 2);

            $subtotal = bcadd($subtotal, $lineBase, 2);
            $discountTotal = bcadd($discountTotal, $discountAmount, 2);
            $taxTotal = bcadd($taxTotal, $taxAmount, 2);

            $normalizedItems[] = [
                'description' => $item['description'] ?? 'Line Item '.($index + 1),
                'item_id' => $item['item_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_type' => $discountType,
                'discount_value' => $discountType ? $discountValue : null,
                'discount_amount' => $discountAmount,
                'tax_id' => $item['tax_id'] ?? null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'subtotal' => $lineTotal,
                'sort_order' => $index,
            ];
        }

        $grandTotal = bcadd(bcsub($subtotal, $discountTotal, 2), $taxTotal, 2);

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
            'items' => $normalizedItems,
        ];
    }
}
