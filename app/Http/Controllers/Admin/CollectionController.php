<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** New Launches, Best Sellers, Demi-Fine, Ethnic, Western, 925 Silver and the other curated groupings. */
class CollectionController extends Controller
{
    use HandlesImages;

    public function index(): View
    {
        $collections = Collection::query()->orderBy('sort_order')->get();
        $collections->each(fn ($c) => $c->setAttribute('product_total', $c->productQuery()->count()));

        return view('admin.collections.index', ['collections' => $collections]);
    }

    public function create(): View
    {
        return $this->form(new Collection(['is_active' => true, 'rule' => 'manual']));
    }

    public function store(Request $request): RedirectResponse
    {
        $collection = new Collection;
        $collection->fill($this->validated($request, $collection))->save();
        $this->syncProducts($request, $collection);

        return redirect()->route('admin.collections.index')->with('ok', 'Collection created.');
    }

    public function edit(Collection $collection): View
    {
        return $this->form($collection);
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $collection->update($this->validated($request, $collection));
        $this->syncProducts($request, $collection);

        return redirect()->route('admin.collections.index')->with('ok', 'Collection updated.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        Media::delete($collection->image);
        $collection->delete();

        return back()->with('ok', 'Collection deleted.');
    }

    private function form(Collection $collection): View
    {
        return view('admin.collections.form', [
            'collection' => $collection,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'is_active']),
            'selected' => $collection->exists ? $collection->products()->pluck('products.id')->all() : [],
        ]);
    }

    private function syncProducts(Request $request, Collection $collection): void
    {
        if ($collection->rule === 'manual') {
            $collection->products()->sync(array_map('intval', (array) $request->input('products', [])));
        }
    }

    private function validated(Request $request, Collection $collection): array
    {
        $request->merge(['handle' => Str::slug($request->input('handle') ?: $request->input('title'))]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'handle' => ['required', 'max:80', Rule::unique('collections')->ignore($collection), Rule::unique('categories', 'slug')],
            'blurb' => ['nullable', 'string', 'max:255'],
            'rule' => ['required', Rule::in(array_keys(Collection::RULES))],
            'menu_group' => ['nullable', Rule::in(array_keys(Collection::MENU_GROUPS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'products' => ['array'],
            'products.*' => ['integer', 'exists:products,id'],
            'image' => $this->imageRule(),
        ]);
        unset($data['products']);
        $data['image'] = $this->image($request, 'image', $collection->image, 'collections');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
