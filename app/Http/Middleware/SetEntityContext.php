<?php

namespace App\Http\Middleware;

use App\Models\Entity;
use App\Support\EntityContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEntityContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $routeEntity = $request->route('entity');
        $entity = $routeEntity instanceof Entity ? $routeEntity : null;

        if (! $entity && is_string($routeEntity)) {
            $entity = Entity::query()->find($routeEntity);
        }

        if (! $entity && $request->session()->has('active_entity_id')) {
            $entity = Entity::query()->find((string) $request->session()->get('active_entity_id'));
        }

        if (! $entity) {
            if ($user->isSuperAdmin()) {
                $entity = Entity::query()->orderBy('name')->first();
            } else {
                $entity = $user->entities()->orderBy('name')->first();
            }
        }

        if ($entity && ! $user->isSuperAdmin() && ! $user->hasEntityAccess($entity)) {
            abort(403, 'You are not assigned to the selected entity.');
        }

        /** @var EntityContext $context */
        $context = app(EntityContext::class);
        $context->setEntity($entity);

        if ($entity) {
            $request->session()->put('active_entity_id', $entity->getKey());
        }

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($entity?->getKey());
        }

        return $next($request);
    }
}
