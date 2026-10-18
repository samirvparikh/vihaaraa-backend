<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->order_number,
            'createdAt' => $this->created_at?->toIso8601String(),
            'customer' => [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'landmark' => $this->landmark,
                'city' => $this->city,
                'state' => $this->state,
                'pincode' => $this->pincode,
                'payment' => $this->payment_method,
            ],
            'lines' => $this->items->map(fn ($i) => [
                'id' => $i->product_id,
                'slug' => $i->product?->slug,
                'sku' => $i->sku,
                'name' => $i->name,
                'size' => $i->size,
                'qty' => $i->qty,
                'price' => $i->price,
                'mrp' => $i->mrp,
            ]),
            'totals' => [
                'itemCount' => $this->item_count,
                'mrpTotal' => $this->mrp_total,
                'subtotal' => $this->subtotal,
                'b1g1Discount' => $this->b1g1_discount,
                'couponDiscount' => $this->coupon_discount,
                'shipping' => $this->shipping,
                'total' => $this->total,
            ],
            'coupon' => $this->coupon_code,
            'statusKey' => $this->status,
            'status' => $this->statusLabel(),
            'paymentStatus' => $this->payment_status,
            'paymentMethod' => Order::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method,
            'trackingNumber' => $this->tracking_number,
            'canCancel' => $this->isCancellable(),
        ];
    }
}
