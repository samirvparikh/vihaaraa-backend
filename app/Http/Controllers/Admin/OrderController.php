<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.orders.index', [
            'orders' => $this->filtered($request)->withCount('items')->paginate(25)->withQueryString(),
            'counts' => Order::query()->select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items.product', 'user')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'payment_status' => ['required', Rule::in(array_keys(Order::PAYMENT_STATUSES))],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($order->status === 'cancelled' && $data['status'] !== 'cancelled') {
            return back()->withErrors(['status' => 'A cancelled order cannot be re-opened; its stock has already been returned.']);
        }

        DB::transaction(function () use ($order, $data) {
            if ($data['status'] === 'cancelled') {
                $order->cancel(); // restocks the items
                unset($data['status']);
                if ($data['payment_status'] === 'paid') {
                    unset($data['payment_status']);
                }
            }
            $order->update($data);
        });

        return back()->with('ok', 'Order updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->filtered($request)->with('items')->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Date', 'Customer', 'Phone', 'Email', 'City', 'State', 'Pincode', 'Items', 'Payment', 'Payment status', 'Status', 'Coupon', 'Total']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_number, $o->created_at->format('Y-m-d H:i'), $o->name, $o->phone, $o->email, $o->city, $o->state, $o->pincode,
                    $o->items->map(fn ($i) => "{$i->qty} × {$i->name}".($i->size ? " ({$i->size})" : ''))->implode('; '),
                    Order::PAYMENT_METHODS[$o->payment_method] ?? $o->payment_method, $o->payment_status, $o->statusLabel(), $o->coupon_code, $o->total,
                ]);
            }
            fclose($out);
        }, 'vihaaraa-orders-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filtered(Request $request)
    {
        $q = Order::query()->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('payment')) {
            $q->where('payment_method', $request->string('payment'));
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->date('to'));
        }
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $q->where(fn ($w) => $w->where('order_number', 'like', $term)->orWhere('name', 'like', $term)
                ->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
        }

        return $q;
    }
}
