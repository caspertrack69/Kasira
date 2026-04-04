<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; }
            .page { padding: 24px; }
            .panel { border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e2e8f0; padding: 8px; }
            th { background: #f8fafc; text-align: left; }
            .grid { width: 100%; display: table; table-layout: fixed; border-spacing: 12px; }
            .cell { display: table-cell; width: 25%; vertical-align: top; }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="panel">
                <h1 style="margin:0 0 4px; font-size:20px;">Financial Summary</h1>
                <p style="margin:0; color:#64748b;">Scope {{ $entityName ?? 'All Entities' }}</p>
                <p style="margin:4px 0 0; color:#64748b;">Period {{ $from }} to {{ $to }}</p>
            </div>

            <div class="grid">
                <div class="cell panel"><strong>Invoice Total</strong><div style="margin-top:8px; font-size:18px;">{{ number_format((float) $summary['invoice_total'], 2) }}</div></div>
                <div class="cell panel"><strong>Payment Total</strong><div style="margin-top:8px; font-size:18px;">{{ number_format((float) $summary['payment_total'], 2) }}</div></div>
                <div class="cell panel"><strong>Outstanding</strong><div style="margin-top:8px; font-size:18px;">{{ number_format((float) $summary['outstanding_total'], 2) }}</div></div>
                <div class="cell panel"><strong>Current Aging</strong><div style="margin-top:8px; font-size:18px;">{{ number_format((float) $summary['aging']['current'], 2) }}</div></div>
            </div>

            <div class="panel">
                <h2 style="margin:0 0 12px; font-size:16px;">Aging Breakdown</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Bucket</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Current</td><td style="text-align:right;">{{ number_format((float) $summary['aging']['current'], 2) }}</td></tr>
                        <tr><td>1-30 Days</td><td style="text-align:right;">{{ number_format((float) $summary['aging']['30'], 2) }}</td></tr>
                        <tr><td>31-60 Days</td><td style="text-align:right;">{{ number_format((float) $summary['aging']['60'], 2) }}</td></tr>
                        <tr><td>90+ Days</td><td style="text-align:right;">{{ number_format((float) $summary['aging']['90_plus'], 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
