<?php

use App\Http\Controllers\PdfController;
use App\Models\Invoice;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(PdfController::class)
  ->prefix('generate')
  ->name('generate.')
  ->group(function () {
    Route::get('invoice/{invoice:code}', 'invoice')->name('invoice');
    Route::get('profit-loss/{profitloss:code}', 'profit-loss')->name('profit-loss');
  });

// Route::get('/', function () {
//   return view('welcome');
// });