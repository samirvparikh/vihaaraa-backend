<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $live = Order::query()->whereNotIn('status', ['cancelled', 'returned']);

        return view('admin.dashboard', [
            'stats' => [
                'Revenue (all time)' => '₹'.number_format((clone $live)->sum('total')),
                'Revenue (this month)' => '₹'.number_format((clone $live)->where('created_at', '>=', now()->startOfMonth())->sum('total')),
                'Orders today' => Order::query()->whereDate('created_at', today())->count(),
                'To ship' => Order::query()->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
                'Customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
                'Active products' => Product::query()->active()->count(),
            ],
            'recentOrders' => Order::query()->latest()->limit(8)->get(),
            'lowStock' => Product::query()->active()->where('stock', '<=', 5)->orderBy('stock')->limit(8)->get(),
        ]);
    }
}
