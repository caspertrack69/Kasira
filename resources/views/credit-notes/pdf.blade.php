<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; }
            .page { padding: 24px; }
            .summary { width: 100%; border-collapse: collapse; margin-top: 16px; }
            .summary td { padding: 8px 0; }
            .muted { color: #64748b; font-size: 11px; }
        </style>
    </head>
    <body>
        <div class="page">
            <h1 style="margin: 0; font-size: 20px;">Credit Note {{ $creditNote->credit_note_number }}</h1>
            <p class="muted" style="margin: 4px 0 0;">{{ $creditNote->entity?->name }} | Invoice {{ $creditNote->invoice?->invoice_number }}</p>

            <table class="summary">
                <tr>
                    <td>
                        <strong>Customer</strong><br>
                        {{ $creditNote->customer?->name ?? '-' }}<br>
                        <span class="muted">{{ $creditNote->customer?->email ?? '-' }}</span>
                    </td>
                    <td style="text-align: right;">
                        <strong>Status</strong><br>
                        {{ strtoupper($creditNote->status) }}
                    </td>
                </tr>
            </table>

            <table class="summary">
                <tr>
                    <td class="muted">Credit Amount</td>
                    <td style="text-align: right;"><strong>{{ number_format((float) $creditNote->amount, 2) }}</strong></td>
                </tr>
                <tr>
                    <td class="muted">Reason</td>
                    <td style="text-align: right;">{{ $creditNote->reason }}</td>
                </tr>
                <tr>
                    <td class="muted">Issued At</td>
                    <td style="text-align: right;">{{ $creditNote->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            </table>
        </div>
    </body>
</html>
