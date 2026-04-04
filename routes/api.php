<?php

use App\Http\Controllers\Api\CustomerSearchController;
use App\Http\Controllers\Api\ItemSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'entity.context'])
    ->prefix('internal/entities/{entity}')
    ->group(function (): void {
        Route::get('customers/search', CustomerSearchController::class)->name('api.internal.customers.search');
        Route::get('items/search', ItemSearchController::class)->name('api.internal.items.search');
    });
