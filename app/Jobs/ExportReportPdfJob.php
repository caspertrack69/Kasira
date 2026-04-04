<?php

namespace App\Jobs;

use App\Models\Entity;
use App\Services\Report\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ExportReportPdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $entityId,
        public string $from,
        public string $to,
        public int $requestedBy,
    ) {
    }

    public function handle(FinancialReportService $reportService): void
    {
        $summary = $reportService->summary($this->entityId, $this->from, $this->to);
        $entityName = $this->entityId
            ? Entity::query()->withoutGlobalScopes()->find($this->entityId)?->name
            : 'All Entities';

        $pdf = Pdf::loadView('reports.pdf-summary', [
            'summary' => $summary,
            'from' => $this->from,
            'to' => $this->to,
            'entityName' => $entityName,
        ]);

        Storage::disk('private')->put(
            self::filePathFor($this->requestedBy, $this->entityId, $this->from, $this->to),
            $pdf->output(),
        );
    }

    public static function filePathFor(int $requestedBy, ?string $entityId, string $from, string $to): string
    {
        $scopeKey = $entityId ?: 'all';

        return sprintf('reports/%s/%s_%s_%s.pdf', $requestedBy, $scopeKey, $from, $to);
    }
}
