<?php

namespace App\Jobs;

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

        $pdf = Pdf::loadView('reports.pdf-summary', [
            'summary' => $summary,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        $file = sprintf('reports/%s_%s_%s.pdf', $this->requestedBy, $this->from, $this->to);
        Storage::disk('private')->put($file, $pdf->output());
    }
}
