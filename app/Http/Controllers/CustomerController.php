<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.index', [
            'customers' => Customer::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'customer_number' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'tax_id' => ['nullable', 'string', 'max:60'],
            'billing_address' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Customer::query()->create([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Customer created.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'customer_number' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'tax_id' => ['nullable', 'string', 'max:60'],
            'billing_address' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update($validated);

        return back()->with('status', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return back()->with('status', 'Customer archived.');
    }
}
