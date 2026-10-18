<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeItem;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Home page blocks: category circles (New Launches, Best Sellers, Demi-Fine, Ethnic, Western, 925 Silver),
 * "Handpicked For You" tiles and "Shop The Look" cards. "Our Best Sellers" is driven by the product
 * Best Seller flag, with its heading and size set here too.
 */
class HomeItemController extends Controller
{
    use HandlesImages;

    public function index(Request $request): View
    {
        $section = $this->section($request);

        return view('admin.home-items.index', [
            'section' => $section,
            'sections' => HomeItem::SECTIONS + ['bestsellers' => 'Our Best Sellers'],
            'items' => $section === 'bestsellers' ? collect() : HomeItem::query()->where('section', $section)->with('product')->orderBy('sort_order')->orderBy('id')->get(),
            'settings' => Setting::values(),
            'bestsellers' => $section === 'bestsellers' ? Product::query()->where('is_bestseller', true)->orderBy('sort_order')->get() : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form(new HomeItem(['section' => $this->section($request), 'is_active' => true, 'bg' => 'maroon']));
    }

    public function store(Request $request): RedirectResponse
    {
        $item = new HomeItem;
        $item->fill($this->validated($request, $item))->save();

        return redirect()->route('admin.home-items.index', ['section' => $item->section])->with('ok', 'Item added.');
    }

    public function edit(HomeItem $homeItem): View
    {
        return $this->form($homeItem);
    }

    public function update(Request $request, HomeItem $homeItem): RedirectResponse
    {
        $homeItem->update($this->validated($request, $homeItem));

        return redirect()->route('admin.home-items.index', ['section' => $homeItem->section])->with('ok', 'Item updated.');
    }

    public function destroy(HomeItem $homeItem): RedirectResponse
    {
        Media::delete($homeItem->image);
        $homeItem->delete();

        return back()->with('ok', 'Item deleted.');
    }

    /** Section headings and the number of best sellers shown on the home page. */
    public function headings(Request $request): RedirectResponse
    {
        Setting::put($request->validate([
            'handpicked_title' => ['sometimes', 'required', 'string', 'max:80'],
            'bestsellers_title' => ['sometimes', 'required', 'string', 'max:80'],
            'bestsellers_limit' => ['sometimes', 'required', 'integer', 'min:1', 'max:24'],
            'look_title' => ['sometimes', 'required', 'string', 'max:80'],
        ]));

        return back()->with('ok', 'Section settings saved.');
    }

    private function section(Request $request): string
    {
        $section = $request->query('section', 'circles');

        return array_key_exists($section, HomeItem::SECTIONS) || $section === 'bestsellers' ? $section : 'circles';
    }

    private function form(HomeItem $item): View
    {
        return view('admin.home-items.form', [
            'item' => $item,
            'sectionName' => HomeItem::SECTIONS[$item->section],
            'products' => Product::query()->orderBy('name')->pluck('name', 'id'),
            'backdrops' => array_combine(Product::BACKDROPS, array_map('ucfirst', Product::BACKDROPS)),
        ]);
    }

    private function validated(Request $request, HomeItem $item): array
    {
        $data = $request->validate([
            'section' => [$item->exists ? 'prohibited' : 'required', Rule::in(array_keys(HomeItem::SECTIONS))],
            'label' => ['nullable', 'string', 'max:80'],
            'link' => ['nullable', 'string', 'max:255'],
            'line1' => ['nullable', 'string', 'max:80'],
            'line2' => ['nullable', 'string', 'max:80'],
            'italic_first' => ['boolean'],
            'bg' => ['required', Rule::in(Product::BACKDROPS)],
            'product_id' => [($request->input('section', $item->section) === 'look') ? 'required' : 'nullable', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => $this->imageRule(),
        ], ['product_id.required' => 'Pick the product this look links to.']);
        $data['image'] = $this->image($request, 'image', $item->image, 'home');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
