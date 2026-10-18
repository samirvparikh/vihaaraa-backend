<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'label', 'type', 'value', 'min_order', 'max_discount', 'usage_limit', 'per_user_limit',
    'used_count', 'starts_at', 'expires_at', 'is_active',
])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'float',
            'min_order' => 'float',
            'max_discount' => 'float',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function findByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : static::query()->where('code', $code)->first();
    }

    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }
        $off = $this->type === 'percent' ? rtrim(rtrim(number_format($this->value, 2), '0'), '.').'% off' : '₹'.number_format($this->value).' off';

        return $this->min_order > 0 ? $off.' on ₹'.number_format($this->min_order).'+' : $off;
    }

    /**
     * Reason the code cannot be used right now, or null when it can. The minimum order value is
     * checked separately so the cart can keep the code and show "add ₹X more".
     */
    public function unavailableReason(?User $user = null): ?string
    {
        if (! $this->is_active) {
            return 'This code is no longer active.';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'This code is not active yet.';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This code has expired.';
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'This code has reached its usage limit.';
        }
        if ($user && $this->per_user_limit !== null) {
            $used = $user->orders()->where('coupon_code', $this->code)->where('status', '!=', 'cancelled')->count();
            if ($used >= $this->per_user_limit) {
                return 'You have already used this code.';
            }
        }

        return null;
    }

    public function discountFor(float $amount): float
    {
        if ($amount < $this->min_order) {
            return 0;
        }
        $discount = $this->type === 'percent' ? round($amount * $this->value / 100) : $this->value;
        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return min($discount, $amount);
    }

    /** Shape the storefront uses to price the cart locally. */
    public function toPublicArray(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->displayLabel(),
            'type' => $this->type,
            'value' => $this->value,
            'min' => $this->min_order,
            'maxDiscount' => $this->max_discount,
        ];
    }
}
