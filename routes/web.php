<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

$appPath = env('APP_PATH', 'dashboard');

if (filled($appPath) || $appPath !== '/') {
  Route::get('/', function () use ($appPath) {
    return redirect($appPath);
  });
}

Route::controller(PdfController::class)
  ->prefix('generate')
  ->name('generate.')
  ->group(function () {
    Route::get('invoice/{invoice:code}', 'invoice')->name('invoice');
  });

Route::get('/email/verify/{id}/{hash}', fn(EmailVerificationRequest $request) => $request->fulfill())
  ->middleware(['auth', 'signed'])
  ->name('verification.verify');