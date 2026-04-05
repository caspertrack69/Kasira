<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCreditNotePdfJob;
use App\Models\CreditNote;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreditNotePdfController extends Controller
{
    public function __invoke(CreditNote $creditNote): StreamedResponse
    {
        $this->authorize('view', $creditNote);

        if (! $creditNote->pdf_path || ! Storage::disk('private')->exists($creditNote->pdf_path)) {
            GenerateCreditNotePdfJob::dispatchSync($creditNote->getKey());
            $creditNote->refresh();
        }

        abort_unless($creditNote->pdf_path && Storage::disk('private')->exists($creditNote->pdf_path), 404);

        return Storage::disk('private')->download($creditNote->pdf_path, $creditNote->credit_note_number.'.pdf');
    }
}
