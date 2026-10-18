<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Pricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /** Validates a code before it is applied to the cart. The final discount is recomputed at checkout. */
    public function applyCoupon(Request $request): JsonResponse
    {
        $code = $request->validate(['code' => ['required', 'string', 'max:40']])['code'];
        $coupon = Coupon::findByCode($code);
        if (! $coupon) {
            throw ValidationException::withMessages(['code' => 'That code is not valid.']);
        }
        if ($reason = $coupon->unavailableReason(auth('sanctum')->user())) {
            throw ValidationException::withMessages(['code' => $reason]);
        }

        return response()->json(['coupon' => $coupon->toPublicArray(), 'message' => "{$coupon->code} applied — {$coupon->displayLabel()}"]);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()->with('items.product')->latest()->get();

        return response()->json(['orders' => OrderResource::collection($orders)]);
    }

    public function show(Request $request, string $number): JsonResponse
    {
        $order = $request->user()->orders()->where('order_number', $number)->with('items.product')->firstOrFail();

        return response()->json(['order' => new OrderResource($order)]);
    }

    /** Places an order from the customer's server cart. */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'min:8', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'regex:/^[1-9]\d{5}$/'],
            'payment' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'coupon' => ['nullable', 'string', 'max:40'],
        ], [
            'phone.regex' => 'Enter a valid 10-digit mobile number',
            'pincode.regex' => 'Enter a valid 6-digit pincode',
        ]);

        $order = DB::transaction(function () use ($user, $data) {
            $items = $user->cartItems()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            // Lock the rows so two simultaneous checkouts cannot oversell the last piece.
            $products = Product::query()->whereIn('id', $items->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');
            $lines = [];
            foreach ($items as $item) {
                $product = $products[$item->product_id] ?? null;
                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages(['cart' => 'An item in your cart is no longer available. Please review your cart.']);
                }
                if ($product->stock < $item->qty) {
                    throw ValidationException::withMessages(['cart' => "Only {$product->stock} left of “{$product->name}”. Please update your cart."]);
                }
                $lines[] = ['product' => $product, 'size' => $item->size ?: null, 'qty' => $item->qty, 'price' => $product->price, 'mrp' => $product->mrp];
            }

            $coupon = null;
            if (! empty($data['coupon'])) {
                $coupon = Coupon::findByCode($data['coupon']);
                $reason = $coupon ? $coupon->unavailableReason($user) : 'That coupon code is not valid.';
                if ($reason) {
                    throw ValidationException::withMessages(['coupon' => $reason]);
                }
            }

            $totals = Pricing::totals($lines, $coupon);
            if ($data['payment'] === 'cod' && $totals['total'] > (float) Setting::get('cod_limit')) {
                throw ValidationException::withMessages(['payment' => 'COD is available on orders up to ₹'.number_format((float) Setting::get('cod_limit'))]);
            }

            $order = Order::query()->create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email'],
                'address' => $data['address'], 'landmark' => $data['landmark'] ?? null,
                'city' => $data['city'], 'state' => $data['state'], 'pincode' => $data['pincode'],
                'payment_method' => $data['payment'],
                // Online payment is simulated: it is marked paid straight away. Swap in a gateway callback here.
                'payment_status' => $data['payment'] === 'cod' ? 'pending' : 'paid',
                'status' => 'confirmed',
                'coupon_code' => $totals['couponDiscount'] > 0 ? $coupon->code : null,
                'item_count' => $totals['itemCount'],
                'mrp_total' => $totals['mrpTotal'],
                'subtotal' => $totals['subtotal'],
                'b1g1_discount' => $totals['b1g1Discount'],
                'coupon_discount' => $totals['couponDiscount'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id, 'sku' => $line['product']->sku, 'name' => $line['product']->name,
                    'size' => $line['size'], 'qty' => $line['qty'], 'price' => $line['price'], 'mrp' => $line['mrp'],
                ]);
                $line['product']->decrement('stock', $line['qty']);
            }
            if ($order->coupon_code) {
                $coupon->increment('used_count');
            }
            $user->cartItems()->delete();

            return $order;
        });

        return response()->json(['order' => new OrderResource($order->load('items.product'))], 201);
    }

    public function cancel(Request $request, string $number): JsonResponse
    {
        $order = $request->user()->orders()->where('order_number', $number)->firstOrFail();
        if (! $order->isCancellable()) {
            throw ValidationException::withMessages(['order' => 'This order can no longer be cancelled.']);
        }
        $order->cancel();

        return response()->json(['order' => new OrderResource($order->fresh()->load('items.product')), 'message' => 'Order cancelled.']);
    }

    private function orderNumber(): string
    {
        do {
            $number = 'VH'.now()->format('ymd').strtoupper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
