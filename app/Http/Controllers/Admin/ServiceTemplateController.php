<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceTemplate;
use App\Models\TemplateCategory;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ServiceTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceTemplate::with(['category', 'creator', 'checklistItems'])
            ->withCount('checklistItems');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $templates = $query->latest()->paginate(10)->withQueryString();
        $categories = TemplateCategory::all();

        return view('admin.service-templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        $categories = TemplateCategory::where('is_active', true)->get();
        return view('admin.service-templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:template_categories,id',
            'is_active' => 'boolean',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*.description' => 'required|string',
            'checklist_items.*.instructions' => 'nullable|string',
            'checklist_items.*.photo_instructions' => 'nullable|string',
            'checklist_items.*.requires_photo' => 'boolean',
            'checklist_items.*.is_required' => 'boolean',
            'checklist_items.*.file_instructions' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,jpg,jpeg,png,gif,bmp|max:10240',
        ]);
    
        try {
            DB::beginTransaction();
    
            $template = ServiceTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'is_active' => $request->boolean('is_active', true),
                'created_by' => auth()->id(),
                'version' => 1,
            ]);
    
            foreach ($request->input('checklist_items') as $index => $itemData) {
                // Create checklist item without file first
                $item = $template->checklistItems()->create([
                    'description' => $itemData['description'],
                    'instructions' => $itemData['instructions'] ?? null,
                    'photo_instructions' => $itemData['photo_instructions'] ?? null,
                    'requires_photo' => isset($itemData['requires_photo']),
                    'is_required' => isset($itemData['is_required']),
                    'order' => $index + 1,
                ]);
                
                // Handle file upload if present
                if ($request->hasFile("checklist_items.{$index}.file_instructions")) {
                    $file = $request->file("checklist_items.{$index}.file_instructions");
                    $path = $file->store('checklist-instructions', 'public');
                    $item->update(['file_instructions' => $path]);
                }
            }
    
            // Create initial version
            $template->versions()->create([
                'version' => 1,
                'name' => $template->name,
                'description' => $template->description,
                'checklist_items' => $template->checklistItems()->get()->toArray(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'change_notes' => 'Initial version',
            ]);
    
            DB::commit();
    
            return redirect()
                ->route('admin.service-templates.index')
                ->with('success', 'Service template created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating template: ' . $e->getMessage());
        }
    }

    public function edit(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->load(['category', 'checklistItems' => function ($query) {
            $query->orderBy('order');
        }]);
        $categories = TemplateCategory::where('is_active', true)->get();

        return view('admin.service-templates.edit', [
            'template' => $serviceTemplate,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:template_categories,id',
            'is_active' => 'boolean',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*.id' => 'nullable|exists:checklist_items,id',
            'checklist_items.*.description' => 'required|string',
            'checklist_items.*.instructions' => 'nullable|string',
            'checklist_items.*.photo_instructions' => 'nullable|string',
            'checklist_items.*.requires_photo' => 'boolean',
            'checklist_items.*.is_required' => 'boolean',
            'checklist_items.*.remove_file' => 'nullable|boolean',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Update template
            $serviceTemplate->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'is_active' => $request->boolean('is_active', true),
                'version' => $serviceTemplate->version + 1,
            ]);
    
            // Update checklist items
            $existingIds = [];
            foreach ($request->input('checklist_items') as $index => $itemData) {
                if (isset($itemData['id'])) {
                    // Update existing item
                    $item = ChecklistItem::find($itemData['id']);
                    
                    // Handle file removal if requested
                    if (isset($itemData['remove_file']) && $itemData['remove_file'] && $item->file_instructions) {
                        Storage::disk('public')->delete($item->file_instructions);
                        $item->file_instructions = null;
                    }
                    
                    $item->update([
                        'description' => $itemData['description'],
                        'instructions' => $itemData['instructions'] ?? null,
                        'photo_instructions' => $itemData['photo_instructions'] ?? null,
                        'requires_photo' => isset($itemData['requires_photo']),
                        'is_required' => isset($itemData['is_required']),
                        'order' => $index + 1,
                    ]);
                    
                    // Handle file upload if present
                    if ($request->hasFile("checklist_items.{$index}.file_instructions")) {
                        // Remove old file if it exists
                        if ($item->file_instructions) {
                            Storage::disk('public')->delete($item->file_instructions);
                        }
                        
                        $file = $request->file("checklist_items.{$index}.file_instructions");
                        $path = $file->store('checklist-instructions', 'public');
                        $item->update(['file_instructions' => $path]);
                    }
                    
                    $existingIds[] = $item->id;
                } else {
                    // Create new item
                    $newItem = $serviceTemplate->checklistItems()->create([
                        'description' => $itemData['description'],
                        'instructions' => $itemData['instructions'] ?? null,
                        'photo_instructions' => $itemData['photo_instructions'] ?? null,
                        'requires_photo' => isset($itemData['requires_photo']),
                        'is_required' => isset($itemData['is_required']),
                        'order' => $index + 1,
                    ]);
                    
                    // Handle file upload if present
                    if ($request->hasFile("checklist_items.{$index}.file_instructions")) {
                        $file = $request->file("checklist_items.{$index}.file_instructions");
                        $path = $file->store('checklist-instructions', 'public');
                        $newItem->update(['file_instructions' => $path]);
                    }
                    
                    $existingIds[] = $newItem->id;
                }
            }
    
            // Delete removed items (and their files)
            $itemsToDelete = $serviceTemplate->checklistItems()->whereNotIn('id', $existingIds)->get();
            foreach ($itemsToDelete as $item) {
                if ($item->file_instructions) {
                    Storage::disk('public')->delete($item->file_instructions);
                }
                $item->delete();
            }
    
            // Create new version
            $serviceTemplate->versions()->create([
                'version' => $serviceTemplate->version,
                'name' => $serviceTemplate->name,
                'description' => $serviceTemplate->description,
                'checklist_items' => $serviceTemplate->checklistItems()->get()->toArray(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'change_notes' => $request->input('change_notes', 'Template updated'),
            ]);
    
            DB::commit();
    
            return redirect()
                ->route('admin.service-templates.index')
                ->with('success', 'Service template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating template: ' . $e->getMessage());
        }
    }


    /**
 * Show form to duplicate a service template
 */
public function duplicate(ServiceTemplate $serviceTemplate)
{
    // Get categories for the dropdown
    $categories = TemplateCategory::orderBy('name')->get();
    
    // Pre-fill with existing template data but suggest a new name
    $template = $serviceTemplate;
    $oldName = $template->name;
    $template->name = $oldName . ' (Copy)';
    
    return view('admin.service-templates.duplicate', compact('template', 'categories', 'oldName'));
}

/**
 * Store the duplicated template
 */
public function storeDuplicate(Request $request, ServiceTemplate $serviceTemplate)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'nullable|exists:template_categories,id',
        'is_active' => 'boolean',
    ]);

    try {
        DB::beginTransaction();

        // Create new template with the provided data
        $newTemplate = ServiceTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'version' => 1,
        ]);

        // Duplicate all checklist items from the original template
        foreach ($serviceTemplate->checklistItems as $item) {
            // Create a copy of the file instructions if it exists
            $fileInstructionsPath = null;
            if ($item->file_instructions) {
                $originalPath = $item->file_instructions;
                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $newFilename = uniqid() . '.' . $extension;
                $newPath = 'checklist-instructions/' . $newFilename;
                
                // Copy the file to the new location
                if (Storage::disk('public')->exists($originalPath)) {
                    Storage::disk('public')->copy($originalPath, $newPath);
                    $fileInstructionsPath = $newPath;
                }
            }
            
            // Create the new checklist item
            $newTemplate->checklistItems()->create([
                'description' => $item->description,
                'instructions' => $item->instructions,
                'photo_instructions' => $item->photo_instructions,
                'file_instructions' => $fileInstructionsPath,
                'requires_photo' => $item->requires_photo,
                'is_required' => $item->is_required,
                'order' => $item->order,
                'additional_fields' => $item->additional_fields ?? null,
            ]);
        }

        // Create initial version for the new template
        $newTemplate->versions()->create([
            'version' => 1,
            'name' => $newTemplate->name,
            'description' => $newTemplate->description,
            'checklist_items' => $newTemplate->checklistItems()->get()->toArray(),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'change_notes' => 'Duplicated from template "' . $serviceTemplate->name . '"',
        ]);

        DB::commit();

        return redirect()
            ->route('admin.service-templates.index')
            ->with('success', 'Service template duplicated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error duplicating template: ' . $e->getMessage());
    }
}

    public function destroy(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->delete();
        return redirect()
            ->route('admin.service-templates.index')
            ->with('success', 'Service template deleted successfully.');
    }

    public function toggleStatus(ServiceTemplate $serviceTemplate)
    {
        
        $serviceTemplate->update(['is_active' => !$serviceTemplate->is_active]);
        return back()->with('success', 'Template status updated successfully.');
    }

    public function versions(ServiceTemplate $serviceTemplate)
    {
        $versions = $serviceTemplate->versions()
            ->with('creator')
            ->orderByDesc('version')
            ->get();
    
        return view('admin.service-templates.versions', [
            'template' => $serviceTemplate,
            'versions' => $versions
        ]);
    }
}