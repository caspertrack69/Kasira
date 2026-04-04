<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('customers.view'), 403);

        $query = $request->string('q')->toString();

        $results = Customer::query()
            ->select(['id', 'name', 'email'])
            ->when($query !== '', fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Operation successful',
            'errors' => null,
        ]);
    }
}
