<?php

// use Illuminate\Support\Facades\Route;
// use App\Models\Transaction;

// Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');
    
// Route::middleware('auth')->group(function () {

//     Route::get('/transactions/{transaction}', function (Transaction $transaction) {

//         return $transaction;

//     })->can('view', 'transaction');

// });
// require __DIR__ . '/auth.php';


use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Budgets\Index as BudgetsIndex;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Goals\Index as GoalsIndex;
use App\Livewire\Recurring\Index as RecurringIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Transactions\Index as TransactionsIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', DashboardIndex::class)
        ->name('dashboard');

    Route::get('/analytics', AnalyticsIndex::class)
        ->name('analytics');

    Route::get('/transactions', TransactionsIndex::class)
        ->name('transactions');

    Route::get('/accounts', AccountsIndex::class)
        ->name('accounts');

    Route::get('/budgets', BudgetsIndex::class)
        ->name('budgets');

    Route::get('/categories', CategoriesIndex::class)
        ->name('categories');

    Route::get('/goals', GoalsIndex::class)
        ->name('goals');

    Route::get('/recurring', RecurringIndex::class)
        ->name('recurring');

    Route::get('/reports', ReportsIndex::class)
        ->name('reports');

    Route::get('/settings', SettingsIndex::class)
        ->name('settings');

});