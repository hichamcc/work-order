<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\PartInstance;
use App\Models\StockAdjustment;


use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D; 


class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('part_number', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Filter by stock
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock', '>', 0)
                          ->where('stock', '<=', 10); // You can adjust this threshold
                    break;
            }
        }

        // Sort
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $allowedSortFields = ['name', 'part_number', 'stock', 'cost', 'created_at'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $parts = $query->paginate(10)->withQueryString();

        // Get some statistics
        $stats = [
            'total_parts' => Part::count(),
            'active_parts' => Part::where('is_active', true)->count(),
            'out_of_stock' => Part::where('stock', '<=', 0)->count(),
            'total_value' => Part::sum(\DB::raw('stock * cost')),
        ];

        return view('admin.parts.index', compact('parts', 'stats'));
    }

    public function create()
    {
        return view('admin.parts.create');
    }

    public function show(Part $part)
    {
        // Load the part with its related data
        $part->load([
            'partInstances' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'stockAdjustments' => function ($query) {
                $query->with('adjustedBy')->orderBy('created_at', 'desc')->limit(20);
            }
        ]);
    
        // Count instances by status for serialized parts
        $statusCounts = [];
        if ($part->track_serials) {
            $statusCounts = [
                'in_stock' => $part->partInstances()->where('status', 'in_stock')->count(),
                'assigned' => $part->partInstances()->where('status', 'assigned')->count(),
                'used' => $part->partInstances()->where('status', 'used')->count(),
            ];
        }
    
        return view('admin.parts.show', compact('part', 'statusCounts'));
    }
          
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'part_number' => 'required|string|max:50|unique:parts',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'track_serials' => 'sometimes|boolean',
            'serial_generation_type' => 'required_if:track_serials,1|in:automatic,manual',
            'generate_barcodes' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        
        try {
            $part = Part::create([
                'name' => $validated['name'],
                'part_number' => $validated['part_number'],
                'description' => $validated['description'] ?? null,
                'stock' =>  $validated['stock'], 
                'cost' => $validated['cost'],
                'is_active' => $validated['is_active'] ?? false,
                'track_serials' => $validated['track_serials'] ?? false,
            ]);
            
            // If tracking serials and using automatic generation, create part instances
            if ($validated['track_serials'] ?? false) {
                $stockCount = $validated['stock'];
                
                if ($validated['serial_generation_type'] === 'automatic' && $stockCount > 0) {
                    for ($i = 0; $i < $stockCount; $i++) {
                        PartInstance::create([
                            'part_id' => $part->id,
                            'serial_number' => $part->generateSerialNumber(),
                            'status' => 'in_stock',
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            if ($validated['track_serials'] ?? false) {
                if ($validated['serial_generation_type'] === 'automatic') {
                    return redirect()->route('admin.parts.show', $part)
                        ->with('success', "Part created successfully with {$stockCount} auto-generated serial numbers.");
                } else {
                    // For manual entry, redirect to a page to enter the serial numbers
                    return redirect()->route('admin.parts.serials.create', $part)
                        ->with('info', "Please enter serial numbers for the {$validated['stock']} items.");
                }
            } else {
                return redirect()->route('admin.parts.index')
                    ->with('success', 'Part created successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create part: ' . $e->getMessage());
        }
    }

    // Method to display form for manual serial number entry
    public function createSerials(Part $part)
    {
        return view('admin.parts.create-serials', compact('part'));
    }

    // Method to store manually entered serial numbers
    public function storeSerials(Request $request, Part $part)
    {
        $request->validate([
            'serial_numbers' => 'required|array',
            'serial_numbers.*' => 'required|string|distinct|max:50',
        ]);

        DB::beginTransaction();
        
        try {
            $previousStock = $part->stock;
            $newInstances = [];
            
            foreach ($request->serial_numbers as $serialNumber) {
                $instance = PartInstance::create([
                    'part_id' => $part->id,
                    'serial_number' => $serialNumber,
                    'status' => 'in_stock',
                ]);
                $newInstances[] = $instance;
            }
            
            // Update the part's stock count
            //$newStock = $previousStock + count($request->serial_numbers);
            $part->stock = $part->partInstances()->where('status', 'in_stock')->count();
            $part->save();
//            $part->stock = $newStock;
            //$part->save();
            
            // Create a stock adjustment record
            StockAdjustment::create([
                'part_id' => $part->id,
                'adjusted_by' =>  auth()->id(),
                'previous_stock' => $previousStock,
                'new_stock' => $part->stock,
                'adjustment_quantity' => count($request->serial_numbers),
                'adjustment_type' => 'add',
                'notes' => 'Added ' . count($request->serial_numbers) . ' serialized items',
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.parts.show', $part)
                ->with('success', 'Serial numbers added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to add serial numbers: ' . $e->getMessage());
        }
    }
    

      // Method to delete a serial number
      public function destroySerial(PartInstance $partInstance)
      {
          DB::beginTransaction();
          
          try {
              $part = $partInstance->part;
              $previousStock = $part->stock;
              
              // Check if the serial number is in use
              if ($partInstance->status !== 'in_stock') {
                  return back()->with('error', 'Cannot delete a serial number that is currently assigned or used.');
              }
              
              // Create a stock adjustment record
              StockAdjustment::create([
                  'part_id' => $part->id,
                  'part_instance_id' => $partInstance->id, // Reference the specific part instance
                  'adjusted_by' => auth()->id(),
                  'previous_stock' => $previousStock,
                  'new_stock' => $previousStock - 1,
                  'adjustment_quantity' => -1,
                  'adjustment_type' => 'remove',
                  'notes' => 'Removed serialized item: ' . $partInstance->serial_number,
              ]);
              
              // Update the part's stock count
              $part->stock = $previousStock - 1;
              $part->save();
              
              // Delete the part instance
              $partInstance->delete();
              
              DB::commit();
              
              return back()->with('success', 'Serial number deleted successfully.');
          } catch (\Exception $e) {
              DB::rollBack();
              return back()->with('error', 'Failed to delete serial number: ' . $e->getMessage());
          }
      }


      public function assignToWorkOrder(Request $request)
      {
          $request->validate([
              'part_instance_id' => 'required|exists:part_instances,id',
              'work_order_id' => 'required|exists:work_orders,id',
          ]);
          
          DB::beginTransaction();
          
          try {
              $partInstance = PartInstance::findOrFail($request->part_instance_id);
              $part = $partInstance->part;
              
              // Check if the part instance is available
              if ($partInstance->status !== 'in_stock') {
                  return back()->with('error', 'This part is already assigned or used.');
              }
              
              $previousStock = $part->stock;
              
              // Update the part instance
              $partInstance->update([
                  'status' => 'assigned',
                  'work_order_id' => $request->work_order_id,
              ]);
              
              // Update the part's stock count
              $part->stock = $previousStock - 1;
              $part->save();
              
              // Create a stock adjustment record
              StockAdjustment::create([
                  'part_id' => $part->id,
                  'part_instance_id' => $partInstance->id,
                  'adjusted_by' => auth()->id(),
                  'previous_stock' => $previousStock,
                  'new_stock' => $previousStock - 1,
                  'adjustment_quantity' => -1,
                  'adjustment_type' => 'remove',
                  'notes' => 'Assigned serialized item to Work Order #' . $request->work_order_id,
              ]);
              
              DB::commit();
              
              return back()->with('success', 'Part assigned to work order successfully.');
          } catch (\Exception $e) {
              DB::rollBack();
              return back()->with('error', 'Failed to assign part: ' . $e->getMessage());
          }
      }



    // Method to generate and display a barcode
    public function barcode(PartInstance $partInstance)
    {
        $barcode = new DNS1D();
        $barcode->setStorPath(storage_path('app/barcodes'));
        
        return response($barcode->getBarcodePNG($partInstance->serial_number, 'C128', 2, 60))
            ->header('Content-Type', 'image/png');
    }

    // Method to print multiple barcodes
    public function printBarcodesOld(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'instance_ids' => 'required|array',
            'instance_ids.*' => 'exists:part_instances,id',
        ]);
        
        $part = Part::findOrFail($validated['part_id']);
        $instances = PartInstance::whereIn('id', $validated['instance_ids'])->get();
        
        return view('admin.parts.print-barcodes', compact('part', 'instances'));
    }

        /**
         * Display a page with printable barcodes for selected serial numbers
         */
        public function printBarcodes(Request $request, Part $part)
        {
            $serialIds = explode(',', $request->serials);
            $serialInstances = PartInstance::whereIn('id', $serialIds)->get();
            
            return view('admin.parts.print-barcodes', compact('part', 'serialInstances'));
        }



    public function edit(Part $part)
    {
        return view('admin.parts.edit', compact('part'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('parts')->ignore($part->id),
            ],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $part->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.parts.index')
            ->with('success', 'Part updated successfully.');
    }

    public function destroy(Part $part)
    {
        // Check if part is used in any work orders
        if ($part->workOrderParts()->exists()) {
            return back()->with('error', 'Cannot delete part that has been used in work orders.');
        }

        $part->delete();

        return back()->with('success', 'Part deleted successfully.');
    }

    public function adjustStock(Request $request, Part $part)
    {
        $validated = $request->validate([
            'adjustment' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $newStock = $part->stock + $validated['adjustment'];

        if ($newStock < 0) {
            return back()->with('error', 'Cannot adjust stock below 0.');
        }

        $part->update(['stock' => $newStock]);

        // Optionally, you could log this adjustment in a stock_adjustments table
         StockAdjustment::create([
             'part_id' => $part->id,
            'quantity' => $validated['adjustment'],
             'notes' => $validated['notes'],
             'adjusted_by' => auth()->id(),
         ]);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function toggleStatus(Part $part)
    {
        $part->update(['is_active' => !$part->is_active]);

        return back()->with('success', 'Part status updated successfully.');
    }

    public function export()
    {
        $parts = Part::all();
        
        return response()->streamDownload(function() use ($parts) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Name', 'Part Number', 'Description', 'Stock', 'Cost', 'Status']);
            
            // Data
            foreach ($parts as $part) {
                fputcsv($file, [
                    $part->name,
                    $part->part_number,
                    $part->description,
                    $part->stock,
                    $part->cost,
                    $part->is_active ? 'Active' : 'Inactive',
                ]);
            }
            
            fclose($file);
        }, 'parts.csv');
    }

    public function stockHistory(Part $part)
{
    $adjustments = $part->stockAdjustments()
        ->with('adjustedBy')
        ->latest()
        ->paginate(10);

    return view('admin.parts.stock-history', compact('part', 'adjustments'));
}
}