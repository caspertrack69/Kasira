<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\RedirectResponse;

class EntitySwitchController extends Controller
{
    public function __invoke(Entity $entity): RedirectResponse
    {
        if (! request()->user()?->hasEntityAccess($entity)) {
            abort(403);
        }

        session(['active_entity_id' => $entity->getKey()]);

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($entity->getKey());
        }

        return back()->with('status', 'Active entity switched to '.$entity->name.'.');
    }
}
