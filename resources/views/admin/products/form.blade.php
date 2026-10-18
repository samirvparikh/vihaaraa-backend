@extends('admin.layout')
@section('title', $product->exists ? $product->name : 'New product')
@section('content')
    <div class="page-head">
        <h1>{{ $product->exists ? 'Edit product' : 'New product' }}</h1>
        <div style="display:flex;gap:12px;align-items:center">
            @if ($product->exists && $product->is_active)
                <a href="{{ rtrim(config('app.frontend_url'), '/') }}/product/{{ $product->slug }}" target="_blank" rel="noopener">View on store ↗</a>
            @endif
            <a href="{{ route('admin.products.index') }}">← All products</a>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf @if ($product->exists) @method('put') @endif
        <div class="grid-form">
            <div>
                <div class="card">
                    <h2>Details</h2>
                    <x-admin.input name="name" label="Product name" :value="$product->name" required />
                    <div class="row">
                        <x-admin.input name="sku" label="SKU" :value="$product->sku" help="Leave blank to auto-generate." />
                        <x-admin.input name="slug" label="URL slug" :value="$product->slug" help="Leave blank to generate from the name." />
                    </div>
                    <x-admin.select name="category_id" label="Category" :options="$categories" :value="$product->category_id" placeholder="— Choose —" required />
                    <x-admin.textarea name="description" label="Description" :value="$product->description" rows="5" />
                </div>

                <div class="card">
                    <h2>Pricing & stock</h2>
                    <div class="row">
                        <x-admin.input name="price" type="number" step="0.01" min="0" label="Selling price (₹)" :value="$product->price" required />
                        <x-admin.input name="mrp" type="number" step="0.01" min="0" label="MRP (₹)" :value="$product->mrp" required />
                        <x-admin.input name="stock" type="number" min="0" label="Stock" :value="$product->stock" required />
                    </div>
                </div>

                <div class="card">
                    <h2>Material & specifications</h2>
                    <div class="row">
                        <x-admin.input name="base_metal" label="Base metal" :value="$product->base_metal" list="metals" help="Brass, Copper, Alloy, 925 Sterling Silver" />
                        <x-admin.input name="plating" label="Plating" :value="$product->plating" list="platings" help="e.g. 22K Gold Plated" />
                    </div>
                    <x-admin.input name="stones" label="Stones" :value="implode(', ', $product->stones ?? [])" help="Comma-separated, e.g. Kundan, Faux Pearl" />
                    <div class="row">
                        <x-admin.input name="sizes" label="Sizes" :value="implode(', ', $product->sizes ?? [])" help="Comma-separated, e.g. 2.4, 2.6, 2.8. Leave blank if one size." />
                        <x-admin.input name="weight" label="Approx. weight" :value="$product->weight" help="e.g. 58 g" />
                    </div>
                    <x-admin.input name="includes" label="In the box" :value="$product->includes" help="e.g. Necklace + pair of earrings" />
                    <div class="row">
                        <x-admin.input name="rating" type="number" step="0.1" min="0" max="5" label="Rating (0–5)" :value="$product->rating" />
                        <x-admin.input name="reviews_count" type="number" min="0" label="Review count" :value="$product->reviews_count" />
                    </div>
                    <datalist id="metals"><option>Brass</option><option>Copper</option><option>Alloy</option><option>925 Sterling Silver</option></datalist>
                    <datalist id="platings"><option>Gold Plated</option><option>18K Gold Plated</option><option>22K Gold Plated</option><option>24K Gold Plated</option><option>Rhodium Plated</option><option>Silver Plated</option><option>Rose Gold Plated</option><option>Oxidised Silver Plated</option></datalist>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Visibility</h2>
                    <x-admin.check name="is_active" label="Active (visible on store)" :checked="$product->is_active" />
                    <x-admin.check name="is_new" label="New Launch" :checked="$product->is_new" />
                    <x-admin.check name="is_bestseller" label="Best Seller" :checked="$product->is_bestseller" />
                    <x-admin.input name="sort_order" type="number" label="Display order" :value="$product->sort_order ?? 0" min="0" />
                </div>

                <div class="card">
                    <h2>Collections</h2>
                    @php($sel = old('collections', $product->exists ? $product->collections->pluck('id')->all() : []))
                    @foreach ($collections as $c)
                        <label class="check"><input type="checkbox" name="collections[]" value="{{ $c->id }}" @checked(in_array($c->id, $sel))> {{ $c->title }}</label>
                    @endforeach
                    <p class="small muted">New Launches and Best Sellers fill automatically from the flags above.</p>
                </div>

                <div class="card">
                    <h2>Images</h2>
                    <x-admin.image name="image" label="Main image" :path="$product->image" help="Square, at least 800 × 800 px." />
                    <div class="field">
                        <span>Gallery</span>
                        @if ($product->exists && $product->images->count())
                            <div class="gallery">
                                @foreach ($product->images as $img)
                                    <figure>
                                        <img src="{{ $img->url() }}" alt="">
                                        <button class="link-btn small" form="del-img-{{ $img->id }}">Remove</button>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                        <input type="file" name="gallery[]" accept="image/*" multiple>
                        <small>Add up to 10 more photos at once.</small>
                    </div>
                </div>

                <div class="card">
                    <h2>Illustration fallback</h2>
                    <p class="small muted">Until a photo is uploaded, the store draws this piece.</p>
                    <x-admin.select name="shape" label="Shape" :options="$shapes" :value="$product->shape" />
                    <x-admin.select name="stone" label="Stone colour" :options="$stoneColours" :value="$product->stone" />
                    <x-admin.select name="bg" label="Backdrop" :options="$backdrops" :value="$product->bg" />
                </div>
            </div>
        </div>
        <div class="form-actions"><button class="btn">Save product</button><a href="{{ route('admin.products.index') }}">Cancel</a></div>
    </form>

    {{-- Gallery delete forms live outside the main form so they can't nest. --}}
    @if ($product->exists)
        @foreach ($product->images as $img)
            <form id="del-img-{{ $img->id }}" method="post" action="{{ route('admin.products.images.destroy', [$product, $img]) }}" data-confirm="Remove this photo?">@csrf @method('delete')</form>
        @endforeach
    @endif
@endsection
