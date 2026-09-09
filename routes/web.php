<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Agent;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Requester;
use Illuminate\Support\Facades\Route;

// Root → redirect to login
Route::get('/', fn() => redirect()->route('login'));

// Breeze expects a 'dashboard' named route — redirect by role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->hasRole('it_head'))  return redirect()->route('admin.dashboard');
    if ($user?->hasRole('it_staff')) return redirect()->route('agent.dashboard');
    return redirect()->route('requester.dashboard');
})->middleware(['auth', 'active', 'force.password'])->name('dashboard');

// ── Authenticated routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'active', 'force.password'])->group(function () {

    // ── Profile ────────────────────────────────────────────────────────────
    Route::get('/profile',          [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile',        [\App\Http\Controllers\ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Requester dashboard ────────────────────────────────────────────────
    Route::get('/my-dashboard', [Requester\DashboardController::class, 'index'])
         ->name('requester.dashboard')
         ->middleware('role:requester|it_staff|it_head');

    Route::prefix('tickets')->name('requester.tickets.')->middleware('role:requester|it_staff|it_head')->group(function () {
        Route::get('/',                        [Requester\TicketController::class, 'index'])->name('index');
        Route::get('/create',                  [Requester\TicketController::class, 'create'])->name('create');
        Route::post('/',                       [Requester\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}',                [Requester\TicketController::class, 'show'])->name('show');
        Route::get('/{ticket}/edit',           [Requester\TicketController::class, 'edit'])->name('edit');
        Route::patch('/{ticket}',              [Requester\TicketController::class, 'update'])->name('update');
        Route::post('/{ticket}/cancel',        [Requester\TicketController::class, 'cancel'])->name('cancel');
        Route::post('/{ticket}/reopen',        [Requester\TicketController::class, 'reopen'])->name('reopen');
        Route::post('/{ticket}/comments',      [Requester\TicketController::class, 'storeComment'])->name('comments.store');
    });

    // ── IT Staff routes (URL prefix stays /agent/ for backwards compat) ───
    Route::prefix('agent')->name('agent.')->middleware('role:it_staff|it_head')->group(function () {
        Route::get('/dashboard', [Agent\DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/',                   [Agent\TicketController::class, 'index'])->name('index');
            Route::get('/{ticket}',           [Agent\TicketController::class, 'show'])->name('show');
            Route::patch('/{ticket}',         [Agent\TicketController::class, 'update'])->name('update');
            Route::post('/{ticket}/reassign', [Agent\TicketController::class, 'reassign'])->name('reassign');
            Route::post('/{ticket}/comments', [Agent\TicketController::class, 'storeComment'])->name('comments.store');
        });
    });

    // ── IT Head routes (URL prefix stays /admin/ for backwards compat) ────
    Route::prefix('admin')->name('admin.')->middleware('role:it_head')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/',                   [Admin\TicketController::class, 'index'])->name('index');
            Route::get('/{ticket}',           [Admin\TicketController::class, 'show'])->name('show');
            Route::patch('/{ticket}',         [Admin\TicketController::class, 'update'])->name('update');
            Route::delete('/{ticket}',        [Admin\TicketController::class, 'destroy'])->name('destroy');
            Route::post('/{ticket}/comments', [Admin\TicketController::class, 'storeComment'])->name('comments.store');
        });

        // Users
        Route::resource('users', Admin\UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Departments
        Route::get('/departments',              [Admin\DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments',             [Admin\DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [Admin\DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [Admin\DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Categories
        Route::get('/categories',             [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories',            [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}',  [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

        // Priorities / SLA
        Route::get('/priorities',            [Admin\PriorityController::class, 'index'])->name('priorities.index');
        Route::put('/priorities/{priority}', [Admin\PriorityController::class, 'update'])->name('priorities.update');
        Route::delete('/priorities/{priority}', [Admin\PriorityController::class, 'destroy'])->name('priorities.destroy');

        // Reports
        Route::get('/reports',            [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [Admin\ReportController::class, 'exportCsv'])->name('reports.csv');
        Route::get('/reports/export-pdf', [Admin\ReportController::class, 'exportPdf'])->name('reports.pdf');

        // Activity Log (Audit)
        Route::get('/activity-log',            [Admin\ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity-log/export-csv', [Admin\ActivityLogController::class, 'exportCsv'])->name('activity.csv');
        Route::get('/activity-log/export-pdf', [Admin\ActivityLogController::class, 'exportPdf'])->name('activity.pdf');
    });

    // ── Search ─────────────────────────────────────────────────────────────
    Route::get('/search', \App\Http\Controllers\SearchController::class)->name('search');

    // ── Bulk Actions ───────────────────────────────────────────────────────
    Route::post('/tickets/bulk', [\App\Http\Controllers\BulkTicketController::class, 'apply'])
         ->name('tickets.bulk')
         ->middleware('role:it_staff|it_head');

    // ── Shared ─────────────────────────────────────────────────────────────
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'download'])->name('attachments.download');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',               [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read',     [NotificationController::class, 'markRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('readAll');
    });
});

require __DIR__ . '/auth.php';
