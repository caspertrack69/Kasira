<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntityController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Entity::class);

        return view('entities.index', [
            'entities' => Entity::query()->latest('name')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Entity::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:entities,code'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'invoice_prefix' => ['nullable', 'string', 'max:12'],
        ]);

        Entity::query()->create([
            ...$validated,
            'currency' => strtoupper($validated['currency'] ?? 'IDR'),
            'invoice_prefix' => strtoupper($validated['invoice_prefix'] ?? strtoupper($validated['code'])),
        ]);

        return back()->with('status', 'Entity created.');
    }

    public function update(Request $request, Entity $entity): RedirectResponse
    {
        $this->authorize('update', $entity);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'invoice_prefix' => ['nullable', 'string', 'max:12'],
            'default_payment_terms' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $entity->update([
            ...$validated,
            'currency' => strtoupper($validated['currency'] ?? $entity->currency),
            'invoice_prefix' => strtoupper($validated['invoice_prefix'] ?? $entity->invoice_prefix),
            'is_active' => (bool) ($validated['is_active'] ?? $entity->is_active),
        ]);

        return back()->with('status', 'Entity updated.');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        $this->authorize('delete', $entity);

        $entity->update(['is_active' => false]);

        return back()->with('status', 'Entity deactivated.');
    }
}
