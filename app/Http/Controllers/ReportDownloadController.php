<?php

namespace App\Http\Controllers;

use App\Jobs\ExportReportPdfJob;
use App\Models\Entity;
use App\Support\EntityContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportDownloadController extends Controller
{
    public function __invoke(Request $request, EntityContext $context): StreamedResponse
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->toDateString())->toString();
        $entityId = $this->resolveEntityId($request, $context);
        $file = ExportReportPdfJob::filePathFor($request->user()->getKey(), $entityId, $from, $to);

        abort_unless(Storage::disk('private')->exists($file), 404);

        $filename = 'kasira-report-'.$this->scopeKey($entityId).'-'.$from.'-'.$to.'.pdf';

        return Storage::disk('private')->download($file, $filename);
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
