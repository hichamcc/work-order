<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

// Parts API routes
Route::get('/parts/{part}/serials', function (Part $part) {
    return $part->partInstances()
        ->where('status', 'in_stock')
        ->orderBy('created_at', 'desc')
        ->get(['id', 'serial_number']);
})->name('api.parts.serials');