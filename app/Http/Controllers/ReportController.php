<?php

namespace App\Http\Controllers;

use App\Jobs\ExportReportPdfJob;
use App\Services\Report\FinancialReportService;
use App\Support\EntityContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, FinancialReportService $service, EntityContext $context): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();

        $summary = $service->summary($context->id(), $from, $to);

        return view('reports.index', [
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportCsv(Request $request, FinancialReportService $service, EntityContext $context)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();
        $summary = $service->summary($context->id(), $from, $to);

        $csv = implode("\n", [
            'metric,value',
            'invoice_total,'.$summary['invoice_total'],
            'payment_total,'.$summary['payment_total'],
            'outstanding_total,'.$summary['outstanding_total'],
            'aging_current,'.$summary['aging']['current'],
            'aging_30,'.$summary['aging']['30'],
            'aging_60,'.$summary['aging']['60'],
            'aging_90_plus,'.$summary['aging']['90_plus'],
        ]);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kasira-report-'.$from.'-'.$to.'.csv"',
        ]);
    }

    public function exportPdf(Request $request, EntityContext $context)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();

        ExportReportPdfJob::dispatch($context->id(), $from, $to, $request->user()->getKey());

        return back()->with('status', 'Report PDF export job queued.');
    }
}
