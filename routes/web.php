<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\FoodLogController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\OverallReportController;
use App\Http\Controllers\PersonalGoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\SleepLogController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WaterLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes – redirect logged-in users to dashboard
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/', fn () => redirect()->route('login'));

    // Authentication
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function (): void {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Food & Nutrition
    Route::get('/food/reports', [FoodLogController::class, 'reports'])->name('food.reports');
    Route::resource('food', FoodLogController::class)->names('food');

    // Water Tracker
    Route::get('/water', [WaterLogController::class, 'index'])->name('water.index');
    Route::post('/water', [WaterLogController::class, 'store'])->name('water.store');
    Route::post('/water/quick-add', [WaterLogController::class, 'quickAdd'])->name('water.quick-add');
    Route::delete('/water/{waterLog}', [WaterLogController::class, 'destroy'])->name('water.destroy');
    Route::put('/water/goal', [WaterLogController::class, 'updateGoal'])->name('water.update-goal');

    // Money Management
    Route::get('/finance/reports', [FinanceReportController::class, 'index'])->name('finance.reports');
    Route::get('/finance/export-csv', [FinanceReportController::class, 'exportCsv'])->name('finance.export-csv');

    Route::get('/finance/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/finance/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::delete('/finance/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::get('/finance/savings', [SavingsGoalController::class, 'index'])->name('savings.index');
    Route::post('/finance/savings', [SavingsGoalController::class, 'store'])->name('savings.store');
    Route::put('/finance/savings/{savingsGoal}', [SavingsGoalController::class, 'update'])->name('savings.update');
    Route::post('/finance/savings/{savingsGoal}/add-funds', [SavingsGoalController::class, 'addFunds'])->name('savings.add-funds');
    Route::delete('/finance/savings/{savingsGoal}', [SavingsGoalController::class, 'destroy'])->name('savings.destroy');

    Route::resource('finance', TransactionController::class)->parameters(['finance' => 'transaction'])->names('finance');

    // Phase 4: Daily Activity, Habit, Exercise & Sleep Tracking Routes
    Route::post('/activities/{activity}/toggle', [ActivityController::class, 'toggleStatus'])->name('activities.toggle');
    Route::resource('activities', ActivityController::class)->except(['create', 'show']);

    Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggle'])->name('habits.toggle');
    Route::resource('habits', HabitController::class)->except(['create', 'show', 'edit']);

    Route::resource('exercise', ExerciseController::class)->except(['create', 'show', 'edit']);

    Route::resource('sleep', SleepLogController::class)
        ->parameters(['sleep' => 'sleepLog'])
        ->except(['create', 'show', 'edit']);

    // Phase 5: Personal Goals & Milestones
    Route::post('/goals/{goal}/milestones', [PersonalGoalController::class, 'addMilestone'])->name('goals.milestones.store');
    Route::post('/goals/milestones/{milestone}/toggle', [PersonalGoalController::class, 'toggleMilestone'])->name('goals.milestones.toggle');
    Route::delete('/goals/milestones/{milestone}', [PersonalGoalController::class, 'destroyMilestone'])->name('goals.milestones.destroy');
    Route::resource('goals', PersonalGoalController::class)->except(['create', 'edit']);

    // Phase 6: Life Reports & Analytics
    Route::get('/reports', [OverallReportController::class, 'index'])->name('reports.index');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
    });
});
