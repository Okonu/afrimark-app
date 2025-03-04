<?php

use App\Http\Controllers\API\DebtorController;
use App\Http\Controllers\API\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Invoice endpoints
Route::post('invoices', [InvoiceController::class, 'store']);
Route::get('invoices/{invoice_number}', [InvoiceController::class, 'show']);
Route::get('invoices/debtor/{kra_pin}', [InvoiceController::class, 'getByDebtorKra']);
Route::get('invoices/business/{kra_pin}', [InvoiceController::class, 'getByBusinessKra']);

// Debtor endpoints
Route::get('debtors', [DebtorController::class, 'index']);
Route::get('debtors/search', [DebtorController::class, 'search']);
Route::get('debtors/{kra_pin}', [DebtorController::class, 'show']);
