<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PaymentMethod::class);

        return view('payment-methods.index', [
            'paymentMethods' => PaymentMethod::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PaymentMethod::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank_transfer,cash,virtual_account,qris,cheque,other'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PaymentMethod::query()->create([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Payment method saved.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank_transfer,cash,virtual_account,qris,cheque,other'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $paymentMethod->update($validated);

        return back()->with('status', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);

        $paymentMethod->delete();

        return back()->with('status', 'Payment method deleted.');
    }
}
