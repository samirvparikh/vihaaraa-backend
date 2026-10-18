<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Shop-wide rules (shipping, COD, offers), contact details, and the newsletter list. */
class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['s' => Setting::values()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'free_shipping_min' => ['required', 'numeric', 'min:0'],
            'shipping_fee' => ['required', 'numeric', 'min:0'],
            'cod_limit' => ['required', 'numeric', 'min:0'],
            'max_qty' => ['required', 'integer', 'min:1', 'max:99'],
            'b1g1_enabled' => ['boolean'],
            'contact_email' => ['required', 'email'],
            'contact_whatsapp' => ['required', 'string', 'max:40'],
            'contact_hours' => ['required', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string', 'max:255'],
            'pinterest_url' => ['nullable', 'string', 'max:255'],
        ]);
        Setting::put($data);

        return back()->with('ok', 'Settings saved.');
    }

    public function subscribers(): View
    {
        return view('admin.subscribers', ['subscribers' => NewsletterSubscriber::query()->latest()->paginate(50)]);
    }

    public function exportSubscribers(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Subscribed']);
            NewsletterSubscriber::query()->oldest()->each(fn ($s) => fputcsv($out, [$s->email, $s->created_at->format('Y-m-d H:i')]));
            fclose($out);
        }, 'vihaaraa-subscribers.csv', ['Content-Type' => 'text/csv']);
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('ok', 'Subscriber removed.');
    }
}
