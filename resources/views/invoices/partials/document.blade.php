<div style="font-family: Arial, sans-serif; color: #0f172a;">
    <div style="display:flex; justify-content:space-between; gap:24px; margin-bottom:24px;">
        <div>
            <h1 style="margin:0; font-size:28px;">{{ $invoice->entity->name ?? config('app.name') }}</h1>
            <p style="margin:4px 0 0; color:#475569;">Invoice {{ $invoice->invoice_number }}</p>
            <p style="margin:4px 0 0; color:#475569;">{{ $invoice->entity->email ?? '' }}</p>
        </div>
        <div style="text-align:right;">
            <p style="margin:0; font-size:12px; color:#64748b;">Invoice Date</p>
            <p style="margin:0 0 8px; font-weight:700;">{{ $invoice->invoice_date?->format('Y-m-d') ?? $invoice->invoice_date }}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">Due Date</p>
            <p style="margin:0 0 8px; font-weight:700;">{{ $invoice->due_date?->format('Y-m-d') ?? $invoice->due_date }}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">Status</p>
            <p style="margin:0; font-weight:700; text-transform:uppercase;">{{ $invoice->status }}</p>
        </div>
    </div>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
        <tr>
            <td style="vertical-align:top; width:50%; padding-right:12px;">
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 8px; font-size:12px; color:#64748b;">Bill To</p>
                    <p style="margin:0; font-weight:700;">{{ $invoice->customer->name ?? '-' }}</p>
                    <p style="margin:4px 0 0; color:#475569;">{{ $invoice->customer->email ?? '-' }}</p>
                    <p style="margin:4px 0 0; color:#475569;">{{ $invoice->customer->billing_address ?? '-' }}</p>
                </div>
            </td>
            <td style="vertical-align:top; width:50%; padding-left:12px;">
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 8px; font-size:12px; color:#64748b;">Invoice Summary</p>
                    <p style="margin:0; color:#475569;">Currency: {{ $invoice->currency }}</p>
                    <p style="margin:4px 0 0; color:#475569;">Amount Paid: {{ number_format((float) $invoice->amount_paid, 2) }}</p>
                    <p style="margin:4px 0 0; color:#475569;">Amount Due: {{ number_format((float) $invoice->amount_due, 2) }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:left;">Description</th>
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:right;">Qty</th>
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:right;">Unit Price</th>
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:right;">Discount</th>
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:right;">Tax</th>
                <th style="border:1px solid #e2e8f0; padding:10px; text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td style="border:1px solid #e2e8f0; padding:10px;">{{ $item->description }}</td>
                    <td style="border:1px solid #e2e8f0; padding:10px; text-align:right;">{{ $item->quantity }}</td>
                    <td style="border:1px solid #e2e8f0; padding:10px; text-align:right;">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td style="border:1px solid #e2e8f0; padding:10px; text-align:right;">{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td style="border:1px solid #e2e8f0; padding:10px; text-align:right;">{{ number_format((float) $item->tax_amount, 2) }}</td>
                    <td style="border:1px solid #e2e8f0; padding:10px; text-align:right;">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:60%; vertical-align:top; padding-right:16px;">
                @if($invoice->notes)
                    <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-bottom:12px;">
                        <p style="margin:0 0 8px; font-weight:700;">Notes</p>
                        <p style="margin:0; color:#475569;">{{ $invoice->notes }}</p>
                    </div>
                @endif

                @if($invoice->terms)
                    <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                        <p style="margin:0 0 8px; font-weight:700;">Terms</p>
                        <p style="margin:0; color:#475569;">{{ $invoice->terms }}</p>
                    </div>
                @endif
            </td>
            <td style="width:40%; vertical-align:top;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr><td style="padding:8px 0;">Subtotal</td><td style="padding:8px 0; text-align:right;">{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
                    <tr><td style="padding:8px 0;">Discount</td><td style="padding:8px 0; text-align:right;">{{ number_format((float) $invoice->discount_total, 2) }}</td></tr>
                    <tr><td style="padding:8px 0;">Tax</td><td style="padding:8px 0; text-align:right;">{{ number_format((float) $invoice->tax_total, 2) }}</td></tr>
                    <tr><td style="padding:8px 0; font-weight:700; border-top:1px solid #cbd5e1;">Grand Total</td><td style="padding:8px 0; text-align:right; font-weight:700; border-top:1px solid #cbd5e1;">{{ number_format((float) $invoice->grand_total, 2) }}</td></tr>
                    <tr><td style="padding:8px 0;">Amount Paid</td><td style="padding:8px 0; text-align:right;">{{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                    <tr><td style="padding:8px 0; font-weight:700;">Amount Due</td><td style="padding:8px 0; text-align:right; font-weight:700;">{{ number_format((float) $invoice->amount_due, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>
