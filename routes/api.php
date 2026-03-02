<?php

use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\DefectController;
use App\Http\Controllers\FormOptionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('asset-types', AssetTypeController::class);

Route::resource('assets', AssetController::class);

Route::resource('accessories', AccessoryController::class);

Route::resource('defects', DefectController::class);

Route::patch('/transactions/approval-status', [TransactionController::class, 'updateApprovalStatus']);
Route::patch('transactions/{transaction}/approve', [TransactionController::class, 'approve']);
Route::patch('transactions/{transaction}/reject', [TransactionController::class, 'reject']);
Route::resource('transactions', TransactionController::class);

Route::resource('approvers', ApproverController::class);

Route::get('form-options', [FormOptionController::class, 'getPage']);
