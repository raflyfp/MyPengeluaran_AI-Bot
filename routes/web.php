<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Endpoint utama yang dipasang ke Telegram setWebhook.
Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->name('webhooks.telegram');

// Alias webhook cadangan kalau nanti provider/tunnel memakai path /webhooks/telegram.
Route::post('/webhooks/telegram', TelegramWebhookController::class);

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/analytics', AnalyticsController::class)->name('analytics.index');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

    Route::get('/bot', BotController::class)->name('bot.index');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    Route::resource('transactions', TransactionController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/budget', [ProfileController::class, 'editBudget'])->name('profile.budget');
    Route::patch('/profile/budget', [ProfileController::class, 'updateBudget'])->name('profile.budget.update');
    Route::get('/profile/preferences', [ProfileController::class, 'editPreferences'])->name('profile.preferences');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    Route::post('/profile/export-telegram', [ProfileController::class, 'exportTelegram'])->name('profile.export-telegram');
    Route::delete('/profile/telegram', [ProfileController::class, 'disconnectTelegram'])->name('profile.telegram.disconnect');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'callback']);


require __DIR__ . '/auth.php';
