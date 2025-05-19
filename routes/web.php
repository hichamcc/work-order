


<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Admin\ServiceTemplateController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\Settings\CategoryController;
use App\Http\Controllers\Worker\WorkerDashboardController;
use App\Http\Controllers\Worker\WorkerWorkOrderController;
use App\Http\Controllers\Worker\WorkOrderTimeController;
use App\Http\Controllers\Worker\WorkOrderPartController;
use App\Http\Controllers\Worker\WorkOrderPhotoController;
use App\Http\Controllers\Worker\WorkOrderCommentController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/api/parts/{part}/serials', function (App\Models\Part $part) {
    return $part->partInstances()
        ->where('status', 'in_stock')
        ->orderBy('created_at', 'desc')
        ->get(['id', 'serial_number']);
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
        Route::patch('/admin/work-orders/{workOrder}/toggle-invoice', [WorkOrderController::class, 'toggleInvoice'])->name('work-orders.toggle-invoice');

        // Service Templates
        Route::resource('service-templates', ServiceTemplateController::class);
        Route::post('/service-templates/{template}/checklist', [ServiceTemplateController::class, 'updateChecklist'])
            ->name('service-templates.checklist');
            Route::patch('service-templates/{serviceTemplate}/toggle-status', [ServiceTemplateController::class, 'toggleStatus'])
            ->name('service-templates.toggle-status');
            Route::get('service-templates/{serviceTemplate}/duplicate', [ServiceTemplateController::class, 'duplicate'])
            ->name('service-templates.duplicate');
        Route::post('service-templates/{serviceTemplate}/duplicate', [ServiceTemplateController::class, 'storeDuplicate'])
            ->name('service-templates.store-duplicate');
                
        
            Route::get('service-templates/{template}/versions', [ServiceTemplateController::class, 'versions'])
            ->name('service-templates.versions');
    
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportWorkOrders'])->name('reports.export');
    
   
           // Parts Management
        Route::resource('parts', PartController::class);
        
        // Additional Parts Routes
        Route::prefix('parts')->name('parts.')->group(function () {
            Route::get('{part}', [PartController::class, 'show'])
            ->name('show');

            Route::get('{part}/stock-history', [PartController::class, 'stockHistory'])
            ->name('stock-history');
            Route::patch('{part}/toggle-status', [PartController::class, 'toggleStatus'])
                ->name('toggle-status');
                
            Route::patch('{part}/adjust-stock', [PartController::class, 'adjustStock'])
                ->name('adjust-stock');
          
                
            Route::get('export', [PartController::class, 'export'])
                ->name('export');



                 // Serial number management
            Route::get('{part}/serials/create', [PartController::class, 'createSerials'])->name('serials.create');
            Route::post('{part}/serials', [PartController::class, 'storeSerials'])->name('serials.store');
            Route::delete('serials/{partInstance}', [PartController::class, 'destroySerial'])->name('serials.destroy');
            
            // Barcode generation
            //Route::get('barcode/{partInstance}', [PartController::class, 'barcode'])->name('barcode');
            Route::post('barcodes/print', [PartController::class, 'printBarcodes'])->name('barcodes.print');
            // Parts barcode routes
            Route::get('{part}/print-barcodes', [PartController::class, 'printBarcodes'])
            ->name('print-barcodes');

        });

       
   
   
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
       // Dashboard
            Route::get('/dashboard', [WorkerDashboardController::class, 'index'])->name('dashboard');


         

            // Work Orders
            Route::controller(WorkerWorkOrderController::class)->group(function () {
                Route::get('/work-orders', 'index')->name('work-orders.index');
                Route::get('/work-orders/create', 'create')->name('work-orders.create');
                Route::post('/work-orders', 'store')->name('work-orders.store');
                Route::get('/work-orders/{workOrder}', 'show')->name('work-orders.show');
            });

            Route::controller(WorkOrderController::class)->group(function () {
                Route::get('/work-orders/time', 'timeTracking')->name('work-orders.time');
                Route::get('/work-orders/completed', 'completed')->name('work-orders.completed');
            });
         
            // Work Order Time Tracking
            Route::controller(WorkOrderTimeController::class)->group(function () {
                Route::post('/work-orders/{workOrder}/start', 'startWork')->name('work-orders.start-work');
                Route::post('/work-orders/{workOrder}/pause', 'pauseWork')->name('work-orders.pause-work');
                Route::get('/work-orders/{workOrder}/time-tracking',  'timeTrackingView')->name('work-orders.time-tracking');
            });


            Route::patch('/{workOrder}/update-status', [WorkerWorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
            Route::patch('/{workOrder}/checklist-items/{checklistItemId}', [WorkerWorkOrderController::class, 'updateChecklistItem'])->name('work-orders.update-checklist-item');
            Route::post('/{workOrder}/parts', [WorkerWorkOrderController::class, 'addPart'])->name('work-orders.add-part');
            Route::post('/{workOrder}/comments', [WorkerWorkOrderController::class, 'addComment'])->name('work-orders.add-comment');
        

            // Work Order Parts
            Route::controller(WorkOrderPartController::class)->group(function () {
                Route::post('/work-orders/{workOrder}/parts', 'store')->name('work-orders.parts.store');
            });

            // Work Order Photos
            Route::controller(WorkOrderPhotoController::class)->group(function () {
                Route::post('/work-orders/{workOrder}/photos', 'store')->name('work-orders.photos.store');
            });

            // Work Order Comments
            Route::controller(WorkOrderCommentController::class)->group(function () {
                Route::post('/work-orders/{workOrder}/comments', 'store')->name('work-orders.comments.store');
            });

   

     
    });
});

require __DIR__.'/auth.php';
