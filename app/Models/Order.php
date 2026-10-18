<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'name', 'phone', 'email', 'address', 'landmark', 'city', 'state', 'pincode',
    'payment_method', 'payment_status', 'status', 'coupon_code', 'item_count', 'mrp_total', 'subtotal',
    'b1g1_discount', 'coupon_discount', 'shipping', 'total', 'tracking_number', 'admin_note',
])]
class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    public const PAYMENT_STATUSES = ['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'];

    public const PAYMENT_METHODS = [
        'upi' => 'UPI',
        'card' => 'Credit / Debit Card',
        'netbanking' => 'Net Banking',
        'cod' => 'Cash on Delivery',
    ];

    /** Customers may cancel until the parcel leaves the warehouse. */
    public const CANCELLABLE = ['pending', 'confirmed', 'processing'];

    protected function casts(): array
    {
        return [
            'mrp_total' => 'float',
            'subtotal' => 'float',
            'b1g1_discount' => 'float',
            'coupon_discount' => 'float',
            'shipping' => 'float',
            'total' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE, true);
    }

    /** Cancels the order and puts its pieces back in stock. Paid orders are marked for refund. */
    public function cancel(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }
        foreach ($this->items as $item) {
            $item->product?->increment('stock', $item->qty);
        }
        $this->update([
            'status' => 'cancelled',
            'payment_status' => $this->payment_status === 'paid' ? 'refunded' : $this->payment_status,
        ]);
    }
}
