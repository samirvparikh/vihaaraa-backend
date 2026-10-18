<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Policy and info pages served at /pages/{slug} on the storefront. */
class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::query()->orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Page::query()->create($this->validated($request));

        return redirect()->route('admin.pages.index')->with('ok', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.pages.index')->with('ok', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return back()->with('ok', 'Page deleted.');
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('pages')->ignore($page)],
            'body' => ['required', 'string'],
            'show_in_footer' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
