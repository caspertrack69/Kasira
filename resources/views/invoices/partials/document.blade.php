<div>
    <table style="width: 100%; border: none; border-radius: 0; border-spacing: 0; border-collapse: collapse; margin-bottom: 32px;">
        <tr>
            <td style="border: none; padding: 0; vertical-align: top;">
                <h1 class="title">{{ $invoice->entity->name ?? config('app.name') }}</h1>
                <p class="subtitle" style="margin-bottom: 4px;">Invoice <strong class="font-bold" style="color: #0f172a;">#{{ $invoice->invoice_number }}</strong></p>
                <p class="muted" style="margin: 0;">{{ $invoice->entity->email ?? '' }}</p>
            </td>
            <td style="border: none; padding: 0; vertical-align: top; text-align: right;">
                <div style="margin-bottom: 14px;">
                    <span class="badge">{{ $invoice->status }}</span>
                </div>
                <p style="margin: 0 0 4px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Invoice Date</p>
                <p class="font-bold" style="margin: 0 0 12px; font-size: 14px;">{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->invoice_date }}</p>
                
                <p style="margin: 0 0 4px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Due Date</p>
                <p class="font-bold" style="margin: 0; font-size: 14px;">{{ $invoice->due_date?->format('d M Y') ?? $invoice->due_date }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: none; border-radius: 0; border-spacing: 0; border-collapse: collapse; margin-bottom: 24px;">
        <tr>
            <td style="width: 50%; border: none; padding: 0 8px 0 0; vertical-align: top;">
                <div class="panel" style="margin-bottom: 0;">
                    <p style="margin: 0 0 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Bill To</p>
                    <p class="font-bold" style="margin: 0 0 4px; font-size: 15px;">{{ $invoice->customer->name ?? '-' }}</p>
                    <p class="muted" style="margin: 0 0 4px; font-size: 13px;">{{ $invoice->customer->email ?? '-' }}</p>
                    <p class="muted" style="margin: 0; font-size: 13px;">{{ $invoice->customer->billing_address ?? '-' }}</p>
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 0 0 0 8px; vertical-align: top;">
                <div class="panel" style="margin-bottom: 0;">
                    <p style="margin: 0 0 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Payment Summary</p>
                    <table style="width: 100%; border: none; border-radius: 0; border-spacing: 0; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; padding: 0 0 6px 0; color: #64748b; font-size: 13px;">Currency</td>
                            <td style="border: none; padding: 0 0 6px 0; text-align: right; font-weight: 700; color: #0f172a; font-size: 13px;">{{ $invoice->currency }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0; color: #64748b; font-size: 13px;">Amount Paid</td>
                            <td style="border: none; padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a; font-size: 13px;">{{ number_format((float) $invoice->amount_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0 0 0; color: #64748b; font-size: 13px; border-top: 1px solid #f1f5f9;">Amount Due</td>
                            <td style="border: none; padding: 6px 0 0 0; text-align: right; font-weight: 700; color: #e11d48; font-size: 15px; border-top: 1px solid #f1f5f9;">{{ number_format((float) $invoice->amount_due, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 24px;">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <div class="font-bold">{{ $item->description }}</div>
                            @if($item->discount_type)
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ ucfirst($item->discount_type) }} discount</div>
                            @endif
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $item->discount_amount, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $item->tax_amount, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table style="width: 100%; border: none; border-radius: 0; border-spacing: 0; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; border: none; padding: 0 16px 0 0; vertical-align: top;">
                @if($invoice->notes)
                    <div class="panel" style="margin-bottom: 16px; background: #f8fafc;">
                        <p style="margin: 0 0 6px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Notes</p>
                        <p style="margin: 0; color: #334155; font-size: 13px; line-height: 1.5;">{{ $invoice->notes }}</p>
                    </div>
                @endif

                @if($invoice->terms)
                    <div class="panel" style="margin-bottom: 0; background: #f8fafc;">
                        <p style="margin: 0 0 6px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Terms</p>
                        <p style="margin: 0; color: #334155; font-size: 13px; line-height: 1.5;">{{ $invoice->terms }}</p>
                    </div>
                @endif
            </td>
            <td style="width: 45%; border: none; padding: 0; vertical-align: top;">
                <table>
                    <tr>
                        <td style="color: #64748b; font-size: 13px;">Subtotal</td>
                        <td class="text-right font-bold">{{ number_format((float) $invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px;">Discount</td>
                        <td class="text-right font-bold" style="color: #e11d48;">-{{ number_format((float) $invoice->discount_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px;">Tax</td>
                        <td class="text-right font-bold">{{ number_format((float) $invoice->tax_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; font-size: 16px; color: #0f172a; border-bottom: none; border-top: 2px solid #e2e8f0; padding-top: 16px;">Grand Total</td>
                        <td class="text-right" style="font-weight: 700; font-size: 16px; color: #0f172a; border-bottom: none; border-top: 2px solid #e2e8f0; padding-top: 16px;">{{ number_format((float) $invoice->grand_total, 2) }}</td>
                    </tr>
                </table>

                <table style="margin-top: 16px; background: #f8fafc; border-color: #f1f5f9;">
                    <tr>
                        <td style="color: #64748b; font-size: 13px; border-bottom: none;">Amount Paid</td>
                        <td class="text-right font-bold" style="border-bottom: none; color: #10b981;">{{ number_format((float) $invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; font-size: 15px; color: #0f172a; border-bottom: none; border-top: 1px solid #e2e8f0;">Amount Due</td>
                        <td class="text-right" style="font-weight: 700; font-size: 15px; color: #e11d48; border-bottom: none; border-top: 1px solid #e2e8f0;">{{ number_format((float) $invoice->amount_due, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>