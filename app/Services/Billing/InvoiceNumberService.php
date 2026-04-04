<?php

namespace App\Services\Billing;

use App\Models\Entity;
use App\Models\Invoice;

class InvoiceNumberService
{
    public function generate(Entity $entity, \DateTimeInterface $invoiceDate): string
    {
        $prefix = $entity->invoice_prefix ?: strtoupper(substr($entity->code, 0, 6));
        $ym = $invoiceDate->format('Y-m');

        $last = Invoice::query()
            ->withoutGlobalScopes()
            ->where('entity_id', $entity->getKey())
            ->where('invoice_number', 'like', $prefix.'-'.$ym.'-%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $tail = (int) end($parts);
            $next = $tail + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $ym, $next);
    }
}
