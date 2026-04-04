<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Tax::class);

        return view('taxes.index', [
            'taxes' => Tax::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tax::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:40'],
            'type' => ['required', 'in:inclusive,exclusive'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Tax::query()->create([
            ...$validated,
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Tax rate saved.');
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorize('update', $tax);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:40'],
            'type' => ['required', 'in:inclusive,exclusive'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $tax->update($validated);

        return back()->with('status', 'Tax rate updated.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $this->authorize('delete', $tax);

        $tax->delete();

        return back()->with('status', 'Tax rate deleted.');
    }
}
