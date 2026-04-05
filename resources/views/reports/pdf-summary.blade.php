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
            .panel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #ffffff; }
            .grid { width: 100%; display: table; table-layout: fixed; border-spacing: 16px 0; margin-left: -16px; margin-right: -16px; margin-bottom: 24px; }
            .cell { display: table-cell; width: 25%; vertical-align: top; }
            .stat-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; margin-bottom: 8px; }
            .stat-value { font-size: 22px; font-weight: 700; color: #0f172a; }
            .section-title { margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #0f172a; }
            table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
            th, td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
            tr:last-child td { border-bottom: none; }
            th { background: #f8fafc; text-align: left; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
            td { font-size: 14px; color: #334155; }
            .text-right { text-align: right; }
            .font-bold { font-weight: 700; color: #0f172a; }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="header-panel">
                <h1 class="title">Financial Summary</h1>
                <p class="subtitle">Scope: <strong style="color: #0f172a;">{{ $entityName ?? 'All Entities' }}</strong></p>
                <p class="subtitle" style="margin-top: 4px;">Period: <strong style="color: #0f172a;">{{ $from }}</strong> to <strong style="color: #0f172a;">{{ $to }}</strong></p>
            </div>

            <div class="grid">
                <div class="cell">
                    <div class="panel">
                        <div class="stat-label">Invoice Total</div>
                        <div class="stat-value">{{ number_format((float) $summary['invoice_total'], 2) }}</div>
                    </div>
                </div>
                <div class="cell">
                    <div class="panel">
                        <div class="stat-label">Payment Total</div>
                        <div class="stat-value">{{ number_format((float) $summary['payment_total'], 2) }}</div>
                    </div>
                </div>
                <div class="cell">
                    <div class="panel">
                        <div class="stat-label">Outstanding</div>
                        <div class="stat-value">{{ number_format((float) $summary['outstanding_total'], 2) }}</div>
                    </div>
                </div>
                <div class="cell">
                    <div class="panel" style="background: #fff1f2; border-color: #ffe4e6;">
                        <div class="stat-label" style="color: #e11d48;">Current Aging</div>
                        <div class="stat-value" style="color: #e11d48;">{{ number_format((float) $summary['aging']['current'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 16px;">
                <h2 class="section-title">Aging Breakdown</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Bucket</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Current</td>
                            <td class="text-right font-bold">{{ number_format((float) $summary['aging']['current'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>1-30 Days</td>
                            <td class="text-right font-bold">{{ number_format((float) $summary['aging']['30'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #b45309;">31-60 Days</td>
                            <td class="text-right font-bold" style="color: #b45309;">{{ number_format((float) $summary['aging']['60'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #e11d48;">90+ Days</td>
                            <td class="text-right font-bold" style="color: #e11d48;">{{ number_format((float) $summary['aging']['90_plus'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>