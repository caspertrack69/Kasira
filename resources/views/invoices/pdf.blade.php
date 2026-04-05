<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; font-size: 13px; line-height: 1.5; background: #ffffff; }
            .page { padding: 32px; }
            .title { margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
            .subtitle { margin: 0; color: #64748b; font-size: 14px; }
            .panel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #ffffff; margin-bottom: 24px; }
            table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
            th, td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
            tr:last-child td { border-bottom: none; }
            th { background: #f8fafc; text-align: left; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
            td { font-size: 14px; color: #334155; }
            .text-right { text-align: right; }
            .font-bold { font-weight: 700; color: #0f172a; }
            .muted { color: #64748b; font-size: 12px; }
            .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: #f1f5f9; color: #334155; }
        </style>
    </head>
    <body>
        <div class="page">
            @include('invoices.partials.document')
        </div>
    </body>
</html>