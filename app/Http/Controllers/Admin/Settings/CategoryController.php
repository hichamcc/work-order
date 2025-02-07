<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = TemplateCategory::withCount('templates')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.settings.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.settings.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:template_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        TemplateCategory::create($validated);

        return redirect()
            ->route('admin.settings.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(TemplateCategory $category)
    {
        return view('admin.settings.categories.edit', compact('category'));
    }

    public function update(Request $request, TemplateCategory $category)
    {

        $validated = $request->validate([
            'name' => 'required|max:255|unique:template_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()
            ->route('admin.settings.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(TemplateCategory $category)
    {
        if ($category->templates()->exists()) {
            return back()->with('error', 'Cannot delete category that has templates.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}