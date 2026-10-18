<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Home page image slider. */
class HeroSlideController extends Controller
{
    use HandlesImages;

    public function index(): View
    {
        return view('admin.slides.index', ['slides' => HeroSlide::query()->with('product')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return $this->form(new HeroSlide(['is_active' => true, 'cta_text' => 'Shop Now', 'cta_link' => '/collections/all', 'theme' => 'maroon']));
    }

    public function store(Request $request): RedirectResponse
    {
        $slide = new HeroSlide;
        $slide->fill($this->validated($request, $slide))->save();

        return redirect()->route('admin.slides.index')->with('ok', 'Slide added.');
    }

    public function edit(HeroSlide $slide): View
    {
        return $this->form($slide);
    }

    public function update(Request $request, HeroSlide $slide): RedirectResponse
    {
        $slide->update($this->validated($request, $slide));

        return redirect()->route('admin.slides.index')->with('ok', 'Slide updated.');
    }

    public function destroy(HeroSlide $slide): RedirectResponse
    {
        Media::delete($slide->image);
        Media::delete($slide->mobile_image);
        $slide->delete();

        return back()->with('ok', 'Slide deleted.');
    }

    private function form(HeroSlide $slide): View
    {
        return view('admin.slides.form', [
            'slide' => $slide,
            'products' => Product::query()->orderBy('name')->pluck('name', 'id'),
            'themes' => array_combine(Product::BACKDROPS, array_map('ucfirst', Product::BACKDROPS)),
        ]);
    }

    private function validated(Request $request, HeroSlide $slide): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['required', 'string', 'max:40'],
            'cta_link' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'theme' => ['required', Rule::in(Product::BACKDROPS)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => $this->imageRule(),
            'mobile_image' => $this->imageRule(),
        ]);
        $data['image'] = $this->image($request, 'image', $slide->image, 'slides');
        $data['mobile_image'] = $this->image($request, 'mobile_image', $slide->mobile_image, 'slides');
        $data['sort_order'] ??= 0;

        return $data;
    }
}
