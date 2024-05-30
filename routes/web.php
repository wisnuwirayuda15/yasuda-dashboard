<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

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
  });

// Route::get('/', function () {
//   return view('welcome');
// });