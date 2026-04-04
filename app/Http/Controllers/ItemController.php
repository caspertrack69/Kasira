<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Item::class);

        return view('items.index', [
            'items' => Item::query()->with('tax')->latest()->paginate(20),
            'taxes' => Tax::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Item::class);

        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Item::query()->create([
            ...$validated,
            'unit' => $validated['unit'] ?? 'unit',
            'is_taxable' => (bool) ($validated['is_taxable'] ?? true),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Item saved.');
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item->update($validated);

        return back()->with('status', 'Item updated.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return back()->with('status', 'Item archived.');
    }
}
