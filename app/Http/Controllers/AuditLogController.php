<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('audit.view'), 403);

        return view('audit-logs.index', [
            'logs' => AuditLog::query()->latest('created_at')->paginate(25),
        ]);
    }
}
