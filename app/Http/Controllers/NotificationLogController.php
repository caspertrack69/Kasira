<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\View\View;

class NotificationLogController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('notifications.view'), 403);

        return view('notifications.index', [
            'logs' => NotificationLog::query()->latest('created_at')->paginate(25),
        ]);
    }
}
