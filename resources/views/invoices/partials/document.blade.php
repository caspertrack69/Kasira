<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
    <!-- Header -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
        <tr>
            <td style="vertical-align: top;">
                @if($invoice->entity->logo ?? false)
                    <img src="{{ public_path('storage/'.$invoice->entity->logo) }}" style="height: 50px; margin-bottom: 20px;">
                @else
                    <h1 style="margin: 0; font-size: 28px; font-weight: 900; letter-spacing: -1.5px; text-transform: uppercase; color: #000;">{{ $invoice->entity?->name ?? config('app.name') }}</h1>
                @endif
                <div style="font-size: 12px; color: #64748b; line-height: 1.4; margin-top: 15px;">
                    <p style="margin: 0; font-weight: bold; color: #000; font-size: 13px;">{{ $invoice->entity?->name }}</p>
                    <p style="margin: 2px 0;">{{ $invoice->entity?->address }}</p>
                    <p style="margin: 0;">{{ $invoice->entity?->email }}</p>
                    <p style="margin: 2px 0;">{{ $invoice->entity?->phone }}</p>
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <h2 style="margin: 0; font-size: 44px; font-weight: 200; letter-spacing: -3px; color: #000; line-height: 1;">INVOICE</h2>
                <div style="margin-top: 25px;">
                    <p style="margin: 0; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px;">No. Invoice</p>
                    <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000;">{{ $invoice->invoice_number }}</p>
                </div>
                <table style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="text-align: right; padding: 0 15px 0 0; vertical-align: top;">
                            <p style="margin: 0; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px;">Issued</p>
                            <p style="margin: 2px 0 0; font-size: 12px; font-weight: bold; color: #000;">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->invoice_date }}</p>
                        </td>
                        <td style="text-align: right; padding: 0; vertical-align: top; width: 90px;">
                            <p style="margin: 0; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px;">Due Date</p>
                            <p style="margin: 2px 0 0; font-size: 12px; font-weight: bold; color: #000;">{{ $invoice->due_date?->format('d M Y') ?? $invoice->due_date }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Billing Info Row -->
    <table style="width: 100%; border-collapse: collapse; border-top: 1px solid #f1f5f9; border-bottom: 2px solid #000; margin-bottom: 0;">
        <tr>
            <td style="padding: 25px 0; vertical-align: top; width: 55%;">
                <p style="margin: 0 0 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px;">Bill To</p>
                <div style="line-height: 1.4;">
                    <p style="margin: 0; font-size: 17px; font-weight: 800; color: #000;">{{ $invoice->customer?->name ?? '-' }}</p>
                    <p style="margin: 4px 0; font-size: 12px; color: #475569; max-width: 280px;">{{ $invoice->customer?->billing_address ?? '-' }}</p>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">{{ $invoice->customer?->email ?? '-' }}</p>
                </div>
            </td>
            <td style="padding: 25px 0; vertical-align: top; text-align: right;">
                <p style="margin: 0 0 5px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px;">Total Amount Due</p>
                <p style="margin: 0; font-size: 36px; font-weight: 900; color: #000; letter-spacing: -2px; line-height: 1;">{{ $invoice->currency }} {{ number_format((float) $invoice->amount_due, 2) }}</p>
                <p style="margin: 8px 0 0; font-size: 11px; font-weight: 600; color: #64748b;">Payable by {{ $invoice->due_date?->format('d M Y') ?? $invoice->due_date }}</p>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="padding: 12px 0; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #000; letter-spacing: 0.5px;">Description</th>
                <th style="padding: 12px 0; text-align: right; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #000; width: 50px;">Qty</th>
                <th style="padding: 12px 0; text-align: right; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #000; width: 100px;">Price</th>
                <th style="padding: 12px 0; text-align: right; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #000; width: 80px;">Tax</th>
                <th style="padding: 12px 0; text-align: right; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #000; width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="padding: 15px 0; vertical-align: top; border-bottom: 1px solid #f8fafc;">
                    <p style="margin: 0; font-size: 13px; font-weight: bold; color: #000;">{{ $item->description }}</p>
                    @if(($item->discount_amount ?? 0) > 0)
                        <p style="margin: 3px 0 0; font-size: 9px; font-weight: bold; color: #e11d48; text-transform: uppercase;">- {{ number_format((float)$item->discount_amount, 2) }} DISCOUNT</p>
                    @endif
                </td>
                <td style="padding: 15px 0; vertical-align: top; text-align: right; font-size: 13px; border-bottom: 1px solid #f8fafc; color: #475569;">{{ $item->quantity }}</td>
                <td style="padding: 15px 0; vertical-align: top; text-align: right; font-size: 13px; border-bottom: 1px solid #f8fafc; color: #475569;">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td style="padding: 15px 0; vertical-align: top; text-align: right; font-size: 13px; border-bottom: 1px solid #f8fafc; color: #475569;">{{ number_format((float) ($item->tax_amount ?? 0), 2) }}</td>
                <td style="padding: 15px 0; vertical-align: top; text-align: right; font-size: 13px; font-weight: bold; color: #000; border-bottom: 1px solid #f8fafc;">{{ number_format((float) $item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Area -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                @if($invoice->notes)
                    <div style="margin-top: 20px;">
                        <p style="margin: 0 0 5px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px;">Notes</p>
                        <p style="margin: 0; font-size: 11px; color: #64748b; line-height: 1.5; max-width: 300px;">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </td>
            <td style="vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 12px; color: #64748b;">Subtotal</td>
                        <td style="padding: 4px 0; text-align: right; font-size: 12px; font-weight: bold; color: #000;">{{ number_format((float) $invoice->subtotal, 2) }}</td>
                    </tr>
                    @if(($invoice->discount_total ?? 0) > 0)
                    <tr>
                        <td style="padding: 4px 0; font-size: 12px; color: #64748b;">Discount</td>
                        <td style="padding: 4px 0; text-align: right; font-size: 12px; font-weight: bold; color: #e11d48;">-{{ number_format((float) $invoice->discount_total, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 4px 0; font-size: 12px; color: #64748b;">Tax</td>
                        <td style="padding: 4px 0; text-align: right; font-size: 12px; font-weight: bold; color: #000;">{{ number_format((float) $invoice->tax_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; font-size: 13px; font-weight: bold; color: #000; text-transform: uppercase; border-top: 1.5px solid #000;">Amount Due</td>
                        <td style="padding: 12px 0; text-align: right; font-size: 22px; font-weight: 900; color: #000; border-top: 1.5px solid #000; letter-spacing: -1px;">{{ $invoice->currency }} {{ number_format((float) $invoice->amount_due, 2) }}</td>
                    </tr>
                    @if(($invoice->amount_paid ?? 0) > 0)
                    <tr>
                        <td style="padding: 6px 10px; background: #f1f5f9; color: #475569; font-size: 10px; font-weight: bold; text-transform: uppercase; border-radius: 4px 0 0 4px;">Paid</td>
                        <td style="padding: 6px 10px; background: #f1f5f9; color: #059669; text-align: right; font-size: 11px; font-weight: 800; border-radius: 0 4px 4px 0;">-{{ number_format((float) $invoice->amount_paid, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Final Footer -->
    @if($invoice->terms)
    <div style="margin-top: 60px; padding-top: 25px; border-top: 1px solid #f1f5f9; text-align: center;">
        <p style="margin: 0 0 5px; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px;">Terms & Conditions</p>
        <p style="margin: 0; font-size: 10px; color: #94a3b8; line-height: 1.5; max-width: 500px; margin-inline: auto;">{{ $invoice->terms }}</p>
    </div>
    @endif
</div>