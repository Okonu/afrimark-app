<?php

use App\Http\Controllers\API\DebtorController;
use App\Http\Controllers\API\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('invoices', [InvoiceController::class, 'store']);
Route::get('invoices/{invoice_number}', [InvoiceController::class, 'show']);

// Debtor endpoints
Route::get('debtors', [DebtorController::class, 'index']);
Route::get('debtors/search', [DebtorController::class, 'search']);
Route::get('debtors/{kra_pin}', [DebtorController::class, 'show']);
