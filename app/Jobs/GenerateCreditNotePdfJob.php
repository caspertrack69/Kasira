<?php

namespace App\Jobs;

use App\Models\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateCreditNotePdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $creditNoteId)
    {
    }

    public function handle(): void
    {
        $creditNote = CreditNote::query()
            ->withoutGlobalScopes()
            ->with(['entity', 'customer', 'invoice'])
            ->findOrFail($this->creditNoteId);

        $pdf = Pdf::loadView('credit-notes.pdf', ['creditNote' => $creditNote]);

        $path = 'credit-notes/'.$creditNote->entity_id.'/'.$creditNote->credit_note_number.'.pdf';
        Storage::disk('private')->put($path, $pdf->output());

        $creditNote->update(['pdf_path' => $path]);
    }
}
