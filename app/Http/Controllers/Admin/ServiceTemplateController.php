<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceTemplate;
use App\Models\TemplateCategory;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            foreach ($validated['checklist_items'] as $index => $item) {
                $template->checklistItems()->create([
                    'description' => $item['description'],
                    'instructions' => $item['instructions'] ?? null,
                    'photo_instructions' => $item['photo_instructions'] ?? null,

                    'requires_photo' => $item['requires_photo'] ?? false,
                    'is_required' => $item['is_required'] ?? true,
                    'order' => $index + 1,
                ]);
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
            foreach ($validated['checklist_items'] as $index => $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    ChecklistItem::where('id', $item['id'])->update([
                        'description' => $item['description'],
                        'instructions' => $item['instructions'] ?? null,
                        'photo_instructions' => $item['photo_instructions'] ?? null,
                        'requires_photo' => $item['requires_photo'] ?? false,
                        'is_required' => $item['is_required'] ?? true,
                        'order' => $index + 1,
                    ]);
                    $existingIds[] = $item['id'];
                } else {
                    // Create new item
                    $newItem = $serviceTemplate->checklistItems()->create([
                        'description' => $item['description'],
                        'instructions' => $item['instructions'] ?? null,
                        'requires_photo' => $item['requires_photo'] ?? false,
                        'is_required' => $item['is_required'] ?? true,
                        'order' => $index + 1,
                    ]);
                    $existingIds[] = $newItem->id;
                }
            }

            // Delete removed items
            $serviceTemplate->checklistItems()
                ->whereNotIn('id', $existingIds)
                ->delete();

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