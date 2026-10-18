<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    use HandlesImages;

    public function index(Request $request): View
    {
        $q = Product::query()->with('category')->orderBy('sort_order')->orderByDesc('id');

        if ($request->filled('q')) {
            $q->search($request->string('q'));
        }
        if ($request->filled('category')) {
            $q->where('category_id', $request->integer('category'));
        }
        if ($request->filled('collection')) {
            $q->whereHas('collections', fn ($c) => $c->whereKey($request->integer('collection')));
        }
        match ($request->query('flag')) {
            'new' => $q->where('is_new', true),
            'bestseller' => $q->where('is_bestseller', true),
            'hidden' => $q->where('is_active', false),
            default => null,
        };
        if ($request->query('stock') === 'low') {
            $q->where('stock', '<=', 5);
        }

        return view('admin.products.index', [
            'products' => $q->paginate(20)->withQueryString(),
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'collections' => Collection::query()->where('rule', 'manual')->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product([
            'is_active' => true, 'stock' => 100, 'shape' => 'necklace', 'stone' => 'none', 'bg' => 'blush', 'sku' => $this->nextSku(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product;
        $product->fill($this->validated($request, $product))->save();
        $this->afterSave($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load('images', 'collections'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));
        $this->afterSave($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Media::delete($product->image);
        $product->images->each(fn ($img) => Media::delete($img->path));
        $product->delete();

        return redirect()->route('admin.products.index')->with('ok', 'Product deleted.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);
        Media::delete($image->path);
        $image->delete();

        return back()->with('ok', 'Gallery image removed.');
    }

    private function form(Product $product): View
    {
        $options = fn (array $list) => array_combine($list, array_map(fn ($v) => Str::headline($v), $list));

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'collections' => Collection::query()->where('rule', 'manual')->orderBy('sort_order')->get(),
            'shapes' => $options(Product::SHAPES),
            'stoneColours' => array_replace($options(Product::STONES), ['ad' => 'American Diamond / Clear']),
            'backdrops' => $options(Product::BACKDROPS),
        ]);
    }

    private function afterSave(Request $request, Product $product): void
    {
        $product->collections()->sync(array_map('intval', (array) $request->input('collections', [])));

        $next = (int) $product->images()->max('sort_order');
        foreach ((array) $request->file('gallery', []) as $file) {
            $product->images()->create(['path' => $file->store('products', 'public'), 'sort_order' => ++$next]);
        }
    }

    private function validated(Request $request, Product $product): array
    {
        $request->merge([
            'slug' => Str::slug(str_replace('&', 'and', $request->input('slug') ?: (string) $request->input('name'))),
            'sku' => strtoupper(trim((string) $request->input('sku'))) ?: $this->nextSku(),
        ]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'max:180', Rule::unique('products')->ignore($product)],
            'sku' => ['required', 'max:40', Rule::unique('products')->ignore($product)],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_metal' => ['nullable', 'string', 'max:80'],
            'plating' => ['nullable', 'string', 'max:80'],
            'stones' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:40'],
            'includes' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'mrp' => ['required', 'numeric', 'gte:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'shape' => ['required', Rule::in(Product::SHAPES)],
            'stone' => ['required', Rule::in(Product::STONES)],
            'bg' => ['required', Rule::in(Product::BACKDROPS)],
            'is_new' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'collections' => ['array'],
            'collections.*' => ['integer', 'exists:collections,id'],
            'image' => $this->imageRule(),
            'gallery' => ['array', 'max:10'],
            'gallery.*' => $this->imageRule(),
        ], ['mrp.gte' => 'MRP must be at least the selling price.']);

        // Stones and sizes are typed comma-separated in the form and stored as lists.
        $list = fn (?string $v) => array_values(array_filter(array_map('trim', explode(',', (string) $v)), 'strlen'));
        $data['stones'] = $list($data['stones'] ?? null);
        $data['sizes'] = $list($data['sizes'] ?? null) ?: null;
        $data['rating'] ??= 0;
        $data['reviews_count'] ??= 0;
        $data['sort_order'] ??= 0;
        $data['image'] = $this->image($request, 'image', $product->image, 'products');
        unset($data['collections'], $data['gallery']);

        return $data;
    }

    private function nextSku(): string
    {
        $max = Product::query()->where('sku', 'like', 'VH%')->pluck('sku')
            ->map(fn ($s) => (int) substr($s, 2))->max() ?? 0;

        return sprintf('VH%03d', $max + 1);
    }
}
