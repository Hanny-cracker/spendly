<?php

use App\Http\Controllers\Reports\ReportCsvController;
use App\Http\Controllers\Reports\ReportPdfController;
use App\Livewire\Accounts\Create as AccountsCreate;
use App\Livewire\Accounts\Edit as AccountsEdit;
use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Accounts\Show as AccountsShow;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Budgets\Create as BudgetsCreate;
use App\Livewire\Budgets\Edit as BudgetsEdit;
use App\Livewire\Budgets\Index as BudgetsIndex;
use App\Livewire\Budgets\Show as BudgetsShow;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Goals\Create as GoalsCreate;
use App\Livewire\Goals\Edit as GoalsEdit;
use App\Livewire\Goals\Index as GoalsIndex;
use App\Livewire\Goals\Show as GoalsShow;
use App\Livewire\Recurring\Create as RecurringCreate;
use App\Livewire\Recurring\Edit as RecurringEdit;
use App\Livewire\Recurring\Index as RecurringIndex;
use App\Livewire\Recurring\Show as RecurringShow;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Transactions\Create as TransactionsCreate;
use App\Livewire\Transactions\Edit as TransactionsEdit;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Transactions\Show as TransactionsShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('analytics', AnalyticsIndex::class)
    ->middleware('auth')
    ->name('analytics');

Route::get('accounts', AccountsIndex::class)
    ->middleware('auth')
    ->name('accounts');

Route::get('accounts/create', AccountsCreate::class)
    ->middleware('auth')
    ->name('accounts.create');

Route::get('accounts/{account}/edit', AccountsEdit::class)
    ->middleware('auth')
    ->name('accounts.edit');

Route::get('accounts/{account}', AccountsShow::class)
    ->middleware('auth')
    ->name('accounts.show');

Route::get('budgets', BudgetsIndex::class)
    ->middleware('auth')
    ->name('budgets');

Route::get('budgets/create', BudgetsCreate::class)
    ->middleware('auth')
    ->name('budgets.create');

Route::get('budgets/{budget}/edit', BudgetsEdit::class)
    ->middleware('auth')
    ->name('budgets.edit');

Route::get('budgets/{budget}', BudgetsShow::class)
    ->middleware('auth')
    ->name('budgets.show');

Route::get('categories', CategoriesIndex::class)->middleware('auth')->name('categories');
Route::get('categories/create', CategoriesCreate::class)->middleware('auth')->name('categories.create');
Route::get('categories/{category}/edit', CategoriesEdit::class)->middleware('auth')->name('categories.edit');

Route::get('goals', GoalsIndex::class)->middleware('auth')->name('goals');
Route::get('goals/create', GoalsCreate::class)->middleware('auth')->name('goals.create');
Route::get('goals/{goal}/edit', GoalsEdit::class)->middleware('auth')->name('goals.edit');
Route::get('goals/{goal}', GoalsShow::class)->middleware('auth')->name('goals.show');

Route::get('recurring', RecurringIndex::class)->middleware('auth')->name('recurring');
Route::get('recurring/create', RecurringCreate::class)->middleware('auth')->name('recurring.create');
Route::get('recurring/{recurringTransaction}/edit', RecurringEdit::class)->middleware('auth')->name('recurring.edit');
Route::get('recurring/{recurringTransaction}', RecurringShow::class)->middleware('auth')->name('recurring.show');

Route::get('reports/pdf', ReportPdfController::class)->middleware('auth')->name('reports.pdf');
Route::get('reports/csv', ReportCsvController::class)->middleware('auth')->name('reports.csv');
Route::get('reports', ReportsIndex::class)->middleware('auth')->name('reports');

Route::get('transactions', TransactionsIndex::class)
    ->middleware('auth')
    ->name('transactions');

Route::get('transactions/create', TransactionsCreate::class)
    ->middleware('auth')
    ->name('transactions.create');

Route::get('transactions/{transaction}/edit', TransactionsEdit::class)
    ->middleware('auth')
    ->name('transactions.edit');

Route::get('transactions/{transaction}', TransactionsShow::class)
    ->middleware('auth')
    ->name('transactions.show');
require __DIR__.'/auth.php';
