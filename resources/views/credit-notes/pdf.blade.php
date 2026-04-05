<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; font-size: 13px; line-height: 1.5; background: #ffffff; }
            .page { padding: 32px; }
            .header-panel { margin-bottom: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 24px; }
            .title { margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
            .subtitle { margin: 0; color: #64748b; font-size: 14px; }
            .panel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #ffffff; margin-bottom: 24px; }
            .grid { width: 100%; display: table; table-layout: fixed; border-spacing: 16px 0; margin-left: -16px; margin-right: -16px; }
            .cell { display: table-cell; width: 50%; vertical-align: top; }
            .stat-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; margin-bottom: 8px; }
            .stat-value { font-size: 16px; font-weight: 700; color: #0f172a; }
            .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: #f1f5f9; color: #334155; }
            table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
            th, td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
            tr:last-child td { border-bottom: none; }
            th { background: #f8fafc; text-align: left; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
            td { font-size: 14px; color: #334155; }
            .text-right { text-align: right; }
            .font-bold { font-weight: 700; color: #0f172a; }
            .amount-highlight { font-size: 18px; color: #e11d48; }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="header-panel">
                <h1 class="title">Credit Note <span style="color: #64748b;">#{{ $creditNote->credit_note_number }}</span></h1>
                <p class="subtitle"><strong style="color: #0f172a;">{{ $creditNote->entity?->name }}</strong> | Ref. Invoice: <strong style="color: #0f172a;">{{ $creditNote->invoice?->invoice_number }}</strong></p>
            </div>

            <div class="grid" style="margin-bottom: 24px;">
                <div class="cell">
                    <div class="panel" style="margin-bottom: 0;">
                        <div class="stat-label">Customer Details</div>
                        <div class="stat-value" style="margin-bottom: 4px;">{{ $creditNote->customer?->name ?? '-' }}</div>
                        <div style="color: #64748b; font-size: 13px;">{{ $creditNote->customer?->email ?? '-' }}</div>
                    </div>
                </div>
                <div class="cell">
                    <div class="panel" style="margin-bottom: 0;">
                        <div class="stat-label">Document Status</div>
                        <div style="margin-top: 8px;">
                            <span class="badge">{{ strtoupper($creditNote->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 16px;">
                <h2 style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #0f172a;">Credit Details</h2>
                <table>
                    <tbody>
                        <tr>
                            <td style="width: 30%; color: #64748b; font-weight: 600;">Issue Date</td>
                            <td class="font-bold">{{ $creditNote->created_at?->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">Reason</td>
                            <td>{{ $creditNote->reason }}</td>
                        </tr>
                        <tr>
                            <td style="background: #fff1f2; color: #be123c; font-weight: 700;">Credited Amount</td>
                            <td style="background: #fff1f2;" class="text-right font-bold amount-highlight">{{ number_format((float) $creditNote->amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>