<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('settings.manage'), 403);

        return view('settings.index', [
            'settings' => SystemSetting::query()->orderBy('group')->orderBy('key')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.group' => ['required', 'string', 'max:60'],
            'settings.*.key' => ['required', 'string', 'max:120'],
            'settings.*.value' => ['nullable', 'string'],
        ]);

        foreach ($validated['settings'] as $item) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $item['key']],
                ['group' => $item['group'], 'value' => $item['value'] ?? null],
            );
        }

        return back()->with('status', 'Settings saved.');
    }
}
