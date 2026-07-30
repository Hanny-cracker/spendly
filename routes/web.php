<?php

use Illuminate\Support\Facades\Route;
use App\Models\Transaction;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
    
Route::middleware('auth')->group(function () {

    Route::get('/transactions/{transaction}', function (Transaction $transaction) {

        return $transaction;

    })->can('view', 'transaction');

});
require __DIR__ . '/auth.php';
