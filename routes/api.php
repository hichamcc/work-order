<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TruckServiceStatusController;
use App\Models\Part;
use App\Models\PartInstance;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Oil service status for other systems, e.g. colouring truck numbers on the
// delivery site. Read-only and guarded by a shared token.
Route::middleware('api.token')->prefix('trucks')->group(function () {
    Route::get('/service-status', [TruckServiceStatusController::class, 'index'])
        ->name('api.trucks.service-status');
    Route::get('/service-status/{truckNumber}', [TruckServiceStatusController::class, 'show'])
        ->where('truckNumber', '.*')
        ->name('api.trucks.service-status.show');
});

// Parts API routes
Route::get('/parts/{part}/serials', function (Part $part) {
    return $part->partInstances()
        ->where('status', 'in_stock')
        ->orderBy('created_at', 'desc')
        ->get(['id', 'serial_number']);
})->name('api.parts.serials');