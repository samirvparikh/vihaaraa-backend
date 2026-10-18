<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Top offer bar messages that rotate above the storefront header. */
class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', ['items' => Announcement::query()->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['item' => new Announcement(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::query()->create($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('ok', 'Offer message added.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['item' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('ok', 'Offer message updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('ok', 'Offer message deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:160'],
            'link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
