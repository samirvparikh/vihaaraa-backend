<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use HandlesImages;

    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::query()->withCount('products')->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true, 'show_in_menu' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = new Category;
        $category->fill($this->validated($request, $category))->save();

        return redirect()->route('admin.categories.index')->with('ok', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('ok', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Move or delete the products in “'.$category->name.'” before deleting it.']);
        }
        Media::delete($category->image);
        $category->delete();

        return back()->with('ok', 'Category deleted.');
    }

    private function validated(Request $request, Category $category): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            // Shares the /collections/{handle} URL space with collections, so the handle must be unique across both.
            'slug' => ['required', 'max:80', Rule::unique('categories')->ignore($category), Rule::unique('collections', 'handle')],
            'blurb' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_in_menu' => ['boolean'],
            'is_active' => ['boolean'],
            'image' => $this->imageRule(),
        ]);
        $data['image'] = $this->image($request, 'image', $category->image, 'categories');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
