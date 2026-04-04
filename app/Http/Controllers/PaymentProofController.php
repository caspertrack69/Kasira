<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    public function __invoke(Payment $payment): StreamedResponse
    {
        $this->authorize('view', $payment);

        abort_unless($payment->proof_path && Storage::disk('private')->exists($payment->proof_path), 404);

        $extension = pathinfo($payment->proof_path, PATHINFO_EXTENSION);
        $filename = $payment->payment_number.($extension ? '.'.$extension : '');

        return Storage::disk('private')->download($payment->proof_path, $filename);
    }
}
