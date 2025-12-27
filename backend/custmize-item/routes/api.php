<?php

use App\Exports\CustomizationsExport;
use App\Http\Controllers\StandardCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomizationController;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('standard-codes', [StandardCodeController::class, 'index']);
// Route::post('standard-codes', [StandardCodeController::class, 'store']);
// Route::put('standard-codes/{id}', [StandardCodeController::class, 'update']);
// Route::delete('standard-codes/{id}', [StandardCodeController::class, 'destroy']);
Route::post('/customizations/bulk-delete', [CustomizationController::class, 'bulkDelete']);

Route::apiResource('standard-codes', StandardCodeController::class);
Route::apiResource('customizations', CustomizationController::class);
Route::post('/customizations/import', [CustomizationController::class, 'import']);
Route::get('/export-customizations', function () {
    return Excel::download(new CustomizationsExport, 'product_customizations.xlsx');
});

// Route::apiResource('specifications', SpecificationControll::class);