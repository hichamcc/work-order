<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function show()
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['required', 'string', 'max:255', 'unique:parts'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $part = Part::create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.parts.index')
            ->with('success', 'Part created successfully.');
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