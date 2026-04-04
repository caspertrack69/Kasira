<?php

namespace App\Http\Controllers;

use App\Jobs\ExportReportPdfJob;
use App\Models\Entity;
use App\Services\Report\FinancialReportService;
use App\Support\EntityContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, FinancialReportService $service, EntityContext $context): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();
        $entityId = $this->resolveEntityId($request, $context);
        $summary = $service->summary($entityId, $from, $to);

        return view('reports.index', [
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'entities' => $request->user()->isSuperAdmin() ? Entity::query()->orderBy('name')->get() : collect(),
            'selectedEntityId' => $entityId ?? 'all',
            'pdfDownloadUrl' => Storage::disk('private')->exists(ExportReportPdfJob::filePathFor($request->user()->getKey(), $entityId, $from, $to))
                ? route('reports.export.pdf.download', ['from' => $from, 'to' => $to, 'entity_id' => $entityId ?? 'all'])
                : null,
        ]);
    }

    public function exportCsv(Request $request, FinancialReportService $service, EntityContext $context)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();
        $entityId = $this->resolveEntityId($request, $context);
        $summary = $service->summary($entityId, $from, $to);

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
            'Content-Disposition' => 'attachment; filename="kasira-report-'.$this->scopeKey($entityId).'-'.$from.'-'.$to.'.csv"',
        ]);
    }

    public function exportPdf(Request $request, EntityContext $context)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();
        $entityId = $this->resolveEntityId($request, $context);

        ExportReportPdfJob::dispatch($entityId, $from, $to, $request->user()->getKey());

        return back()->with('status', 'Report PDF export job queued.');
    }

    private function resolveEntityId(Request $request, EntityContext $context): ?string
    {
        if (! $request->user()->isSuperAdmin()) {
            return $context->id();
        }

        $requested = $request->string('entity_id')->toString();
        if ($requested === 'all') {
            return null;
        }

        if ($requested !== '' && Entity::query()->whereKey($requested)->exists()) {
            return $requested;
        }

        return $context->id();
    }

    private function scopeKey(?string $entityId): string
    {
        return $entityId ?: 'all';
    }
}
