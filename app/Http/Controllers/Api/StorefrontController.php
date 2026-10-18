<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HeroSlide;
use App\Models\HomeItem;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Public, read-only content for the React storefront. */
class StorefrontController extends Controller
{
    /** Global data every page needs: offer bar, menu, footer links, shop rules. */
    public function site(): JsonResponse
    {
        $s = Setting::values();
        $collections = Collection::query()->active()->get();
        $categories = Category::query()->active()->get();

        return response()->json([
            'announcements' => Announcement::query()->active()->get(['id', 'text', 'link']),
            'menu' => [
                ['title' => 'Shop by Style', 'links' => $this->links($collections->where('menu_group', 'style'))],
                ['title' => 'Shop by Category', 'links' => $categories->where('show_in_menu', true)->map(fn ($c) => ['handle' => $c->slug, 'title' => $c->name])->values()],
                ['title' => 'Shop by Finish', 'links' => $this->links($collections->where('menu_group', 'finish'))],
            ],
            'categories' => $categories->map(fn ($c) => ['handle' => $c->slug, 'title' => $c->name])->values(),
            'footerPages' => Page::query()->where('is_active', true)->where('show_in_footer', true)->orderBy('sort_order')->get(['slug', 'title']),
            'settings' => [
                'freeShippingMin' => (float) $s['free_shipping_min'],
                'shippingFee' => (float) $s['shipping_fee'],
                'codLimit' => (float) $s['cod_limit'],
                'maxQty' => (int) $s['max_qty'],
                'b1g1Enabled' => (bool) $s['b1g1_enabled'],
                'contactEmail' => $s['contact_email'],
                'contactWhatsapp' => $s['contact_whatsapp'],
                'contactHours' => $s['contact_hours'],
                'social' => [
                    'instagram' => $s['instagram_url'], 'facebook' => $s['facebook_url'],
                    'youtube' => $s['youtube_url'], 'pinterest' => $s['pinterest_url'],
                ],
            ],
        ]);
    }

    public function home(): JsonResponse
    {
        $s = Setting::values();
        $items = HomeItem::query()->active()->with(['product' => fn ($q) => $q->with(ProductResource::WITH)])->get()->groupBy('section');
        $bestsellers = Product::query()->active()->where('is_bestseller', true)->with(ProductResource::WITH)
            ->orderBy('sort_order')->limit((int) $s['bestsellers_limit'])->get();

        return response()->json([
            'slides' => HeroSlide::query()->active()->with(['product' => fn ($q) => $q->with(ProductResource::WITH)])->get()->map(fn ($sl) => [
                'id' => $sl->id,
                'eyebrow' => $sl->eyebrow,
                'title' => $sl->title,
                'sub' => $sl->subtitle,
                'cta' => $sl->cta_text,
                'to' => $sl->cta_link,
                'theme' => $sl->theme,
                'image' => Media::url($sl->image),
                'mobileImage' => Media::url($sl->mobile_image),
                'product' => $sl->product ? new ProductResource($sl->product) : null,
            ]),
            'circles' => $this->homeItems($items->get('circles')),
            'handpicked' => ['title' => $s['handpicked_title'], 'items' => $this->homeItems($items->get('handpicked'))],
            'bestsellers' => ['title' => $s['bestsellers_title'], 'products' => ProductResource::collection($bestsellers)],
            // A look card without a product has nothing to link to, so it is skipped.
            'look' => ['title' => $s['look_title'], 'items' => $this->homeItems($items->get('look'))->filter(fn ($i) => $i['product'])->values()],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $q = Product::query()->active()->with(ProductResource::WITH)->orderBy('sort_order');

        if ($request->filled('ids')) {
            $q->whereIn('id', array_filter(explode(',', $request->string('ids'))));
        }
        if ($request->filled('q')) {
            $q->search($request->string('q'));
        }
        if ($request->filled('category')) {
            $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category')));
        }

        return response()->json(['products' => ProductResource::collection($q->limit($request->integer('limit', 200))->get())]);
    }

    public function product(string $slug): JsonResponse
    {
        $product = Product::query()->active()->where('slug', $slug)->with(ProductResource::WITH)->firstOrFail();

        // Related: same category scores 3, every shared collection scores 1.
        $handles = $product->collections->pluck('handle');
        $related = Product::query()->active()->whereKeyNot($product->id)->with(ProductResource::WITH)->get()
            ->sortByDesc(fn ($p) => ($p->category_id === $product->category_id ? 3 : 0) + $p->collections->pluck('handle')->intersect($handles)->count())
            ->take(4)->values();

        return response()->json([
            'product' => new ProductResource($product),
            'related' => ProductResource::collection($related),
        ]);
    }

    /** A collection handle may be a curated collection (ethnic, 925-silver…) or a category slug (earrings…). */
    public function collection(string $handle): JsonResponse
    {
        if ($collection = Collection::query()->where('is_active', true)->where('handle', $handle)->first()) {
            $title = $collection->title;
            $blurb = $collection->blurb;
            $image = $collection->imageUrl();
            $query = $collection->productQuery();
        } else {
            $category = Category::query()->where('is_active', true)->where('slug', $handle)->firstOrFail();
            $title = $category->name;
            $blurb = $category->blurb;
            $image = $category->imageUrl();
            $query = $category->products()->active();
        }

        $products = $query->with(ProductResource::WITH)->orderByDesc('is_bestseller')->orderBy('sort_order')->get();

        return response()->json([
            'collection' => ['handle' => $handle, 'title' => $title, 'blurb' => $blurb, 'image' => $image],
            'products' => ProductResource::collection($products),
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = Page::query()->where('is_active', true)->where('slug', $slug)->firstOrFail();

        return response()->json(['title' => $page->title, 'body' => $page->paragraphs()]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        NewsletterSubscriber::query()->firstOrCreate(['email' => strtolower($data['email'])]);

        return response()->json(['message' => 'Thanks for subscribing! Check your inbox for 10% off.']);
    }

    private function links($collections)
    {
        return $collections->map(fn ($c) => ['handle' => $c->handle, 'title' => $c->title])->values();
    }

    private function homeItems($items)
    {
        return collect($items)->map(fn (HomeItem $i) => [
            'id' => $i->id,
            'label' => $i->label,
            'to' => $i->link ?: ($i->product ? '/product/'.$i->product->slug : '/collections/all'),
            'line1' => $i->line1,
            'line2' => $i->line2,
            'italicFirst' => $i->italic_first,
            'bg' => $i->bg,
            'image' => Media::url($i->image),
            'product' => $i->product && $i->product->is_active ? new ProductResource($i->product) : null,
        ])->values();
    }
}
