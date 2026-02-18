<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\Patient\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes for patients/visitors
Route::get('/', [HomeController::class, 'index'])->name('patient.home');
Route::get('/services', [HomeController::class, 'services'])->name('patient.services');
Route::post('/queue/generate', [HomeController::class, 'generateQueue'])->name('queue.generate');
Route::get('/queue/status/{number}', [HomeController::class, 'queueStatus'])->name('queue.status');

// Display screen route (public)
Route::get('/display', [DisplayController::class, 'index'])->name('display.index');

// Generic dashboard route (redirects based on user role)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Authenticated routes with role-based access
Route::middleware(['auth'])->group(function () {
    // Admin routes
    Route::middleware(['role:admin', '2fa', 'db.ip.whitelist'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('permission:view-dashboard')
            ->name('admin.dashboard');
        Route::resource('admin/services', \App\Http\Controllers\Admin\ServiceController::class)
            ->middleware('permission:manage-services')
            ->names('admin.services');
        Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)
            ->middleware('permission:manage-users')
            ->names('admin.users');
        Route::post('/admin/reset-queue', [\App\Http\Controllers\Admin\QueueController::class, 'reset'])
            ->middleware('permission:reset-queue')
            ->name('admin.reset.queue');
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])
            ->middleware('permission:view-reports')
            ->name('reports.index');

        // IP Whitelist Management
        Route::resource('admin/ip-whitelist', \App\Http\Controllers\Admin\IpWhitelistController::class)
            ->middleware('permission:manage-settings')
            ->names('admin.ip-whitelist');
        Route::post('/admin/ip-whitelist/{id}/toggle-status', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'toggleStatus'])
            ->middleware('permission:manage-settings')
            ->name('admin.ip-whitelist.toggle-status');
    });

    // Operator routes
    Route::middleware(['role:operator', '2fa'])->group(function () {
        Route::get('/operator/dashboard', [OperatorDashboardController::class, 'index'])
            ->middleware('permission:view-operator-dashboard')
            ->name('operator.dashboard');
        Route::get('/operator/services', [\App\Http\Controllers\Operator\ServiceController::class, 'index'])
            ->middleware('permission:view-queues')
            ->name('operator.services');
        Route::get('/operator/queues/{service}', [\App\Http\Controllers\Operator\QueueController::class, 'index'])
            ->middleware('permission:view-queues')
            ->name('operator.queues');
        Route::put('/operator/queue/{queue}/call', [\App\Http\Controllers\Operator\QueueController::class, 'call'])
            ->middleware('permission:call-queue')
            ->name('operator.queue.call');
        Route::put('/operator/queue/{queue}/done', [\App\Http\Controllers\Operator\QueueController::class, 'done'])
            ->middleware('permission:complete-queue')
            ->name('operator.queue.done');
        Route::put('/operator/queue/{queue}/cancel', [\App\Http\Controllers\Operator\QueueController::class, 'cancel'])
            ->middleware('permission:cancel-queue')
            ->name('operator.queue.cancel');
    });

    // Profile routes (available to all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Two-Factor Authentication routes
Route::post('/2fa/verify', function () {
    $validated = request()->validate([
        'one_time_password' => 'required|string|size:6',
    ]);

    $authenticator = app(\PragmaRX\Google2FALaravel\Support\Authenticator::class)->boot(request());

    $user = auth()->user();

    if ($authenticator->verifyAndStoreOneTimePassword($validated['one_time_password'])) {
        return redirect()->intended(config('app.home', '/'));
    }

    return redirect()->back()->withErrors([
        'one_time_password' => __('The provided two-factor authentication code was invalid.')
    ]);
})->middleware(['auth'])->name('2fa.verify');

require __DIR__.'/auth.php';
