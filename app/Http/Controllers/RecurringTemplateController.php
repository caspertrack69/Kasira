<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\RecurringTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringTemplateController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', RecurringTemplate::class);

        return view('recurring-templates.index', [
            'templates' => RecurringTemplate::query()->with('customer')->latest()->paginate(20),
            'customers' => Customer::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RecurringTemplate::class);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:150'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,annually'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'occurrences_limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'next_generate_date' => ['required', 'date'],
            'auto_send' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'template_data' => ['required', 'json'],
        ]);

        RecurringTemplate::query()->create([
            ...$validated,
            'template_data' => json_decode($validated['template_data'], true, flags: JSON_THROW_ON_ERROR),
            'auto_send' => (bool) ($validated['auto_send'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Recurring template saved.');
    }

    public function update(Request $request, RecurringTemplate $recurringTemplate): RedirectResponse
    {
        $this->authorize('update', $recurringTemplate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,annually'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'occurrences_limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'next_generate_date' => ['required', 'date'],
            'auto_send' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $recurringTemplate->update($validated);

        return back()->with('status', 'Recurring template updated.');
    }

    public function destroy(RecurringTemplate $recurringTemplate): RedirectResponse
    {
        $this->authorize('delete', $recurringTemplate);

        $recurringTemplate->delete();

        return back()->with('status', 'Recurring template deleted.');
    }
}
