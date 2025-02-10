


<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Admin\ServiceTemplateController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\Settings\CategoryController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


    Route::middleware(['auth', 'role:admin'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
    });
    
Route::middleware(['auth'])->group(function () {
    // Default route after login - redirects based on role
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('worker.dashboard');
    })->name('dashboard');

    // Profile routes (from Breeze)
  
    
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
        // User Management
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
    
        // Work Orders
        Route::resource('work-orders', WorkOrderController::class);
        Route::post('/work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])
            ->name('work-orders.assign');
        Route::patch('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])
            ->name('work-orders.update-status');
    
        // Service Templates
        Route::resource('service-templates', ServiceTemplateController::class);
        Route::post('/service-templates/{template}/checklist', [ServiceTemplateController::class, 'updateChecklist'])
            ->name('service-templates.checklist');
            Route::patch('service-templates/{serviceTemplate}/toggle-status', [ServiceTemplateController::class, 'toggleStatus'])
            ->name('service-templates.toggle-status');
        
        
            Route::get('service-templates/{template}/versions', [ServiceTemplateController::class, 'versions'])
            ->name('service-templates.versions');
    
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/work-orders', [ReportController::class, 'workOrders'])->name('reports.work-orders');
        Route::get('/reports/workers', [ReportController::class, 'workers'])->name('reports.workers');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    });
    

     // Service Templates
     Route::resource('service-templates', ServiceTemplateController::class);
     Route::post('service-templates/{template}/checklist', [ServiceTemplateController::class, 'updateChecklist'])
         ->name('service-templates.checklist');
     Route::post('service-templates/{serviceTemplate}/duplicate', [ServiceTemplateController::class, 'duplicate'])
         ->name('service-templates.duplicate');
     Route::patch('service-templates/{serviceTemplate}/toggle-status', [ServiceTemplateController::class, 'toggleStatus'])
         ->name('service-templates.toggle-status');
  
     
     // Template Categories
    /// Route::resource('template-categories', TemplateCategoryController::class);
     
     // Checklist Items
    // Route::post('checklist-items/reorder', [ChecklistItemController::class, 'reorder'])->name('checklist-items.reorder');
     //Route::resource('checklist-items', ChecklistItemController::class)->except(['index', 'show']);

         

    // Worker routes
    Route::middleware(['role:worker'])->prefix('worker')->name('worker.')->group(function () {
        // Worker Dashboard
        Route::get('/dashboard', function () {
            return view('worker.dashboard');
        })->name('dashboard');

            //Route::get('/work-orders', [WorkerWorkOrderController::class, 'index'])->name('work-orders');
            //Route::get('/time-tracking', [WorkerTimeTrackingController::class, 'index'])->name('time-tracking');
            //Route::get('/completed-orders', [WorkerWorkOrderController::class, 'completed'])->name('completed-orders');


        // Future routes for worker features will go here
        // Assigned Work Orders routes
        // Time Tracking routes
        // Parts Usage routes
    });
});

require __DIR__.'/auth.php';
