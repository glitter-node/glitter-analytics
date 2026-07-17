<?php

use Illuminate\Support\Facades\Route;
use Modules\Glitter\Analytics\Http\Controllers\Api\Admin\AnalyticsSettingsController;
use Modules\Glitter\Analytics\Http\Controllers\Api\Admin\AnalyticsVisitController;
use Modules\Glitter\Analytics\Http\Controllers\Api\Admin\AnalyticsVisitExportController;
use Modules\Glitter\Analytics\Http\Controllers\Api\Admin\AnalyticsDashboardController;
use Modules\Glitter\Analytics\Http\Controllers\Api\Admin\AnalyticsStatusController;
use Modules\Glitter\Analytics\Http\Controllers\Api\Public\PageViewController;
use Modules\Glitter\Analytics\Http\Middleware\EnsureSameOrigin;

Route::post('pageviews', [PageViewController::class, 'store'])
    ->middleware(['optional.sanctum', EnsureSameOrigin::class, 'throttle:120,1'])
    ->name('pageviews.store');

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->name('admin.')->group(function (): void {
    Route::get('dashboard', [AnalyticsDashboardController::class, 'show'])->middleware('permission:admin,glitter-analytics.dashboard.view')->name('dashboard.show');
    Route::get('visits', [AnalyticsVisitController::class, 'index'])->middleware('permission:admin,glitter-analytics.visits.view')->name('visits.index');
    Route::get('visits/export', AnalyticsVisitExportController::class)->middleware('permission:admin,glitter-analytics.visits.view')->name('visits.export');
    Route::get('status', [AnalyticsStatusController::class, 'show'])->middleware('permission:admin,glitter-analytics.dashboard.view')->name('status.show');
    Route::get('settings', [AnalyticsSettingsController::class, 'show'])->middleware('permission:admin,glitter-analytics.settings.view')->name('settings.show');
    Route::put('settings', [AnalyticsSettingsController::class, 'update'])->middleware('permission:admin,glitter-analytics.settings.update')->name('settings.update');
});
