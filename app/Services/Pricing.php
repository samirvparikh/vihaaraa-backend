<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Setting;

/**
 * Server-side twin of the storefront's computeTotals() (src/utils/pricing.js). Orders are always
 * priced here, so a tampered client cannot change what is charged.
 */
class Pricing
{
    /**
     * @param  iterable<array{price: float, mrp: float, qty: int}>  $lines
     */
    public static function totals(iterable $lines, ?Coupon $coupon = null): array
    {
        $units = [];
        $mrpTotal = 0;
        foreach ($lines as $line) {
            for ($i = 0; $i < $line['qty']; $i++) {
                $units[] = (float) $line['price'];
            }
            $mrpTotal += $line['mrp'] * $line['qty'];
        }
        rsort($units);

        $itemCount = count($units);
        $subtotal = array_sum($units);

        // Buy 1 Get 1: sorted high → low, every second unit is free, so the cheaper item of each pair is on us.
        $b1g1 = 0;
        if (Setting::get('b1g1_enabled')) {
            foreach ($units as $i => $price) {
                if ($i % 2 === 1) {
                    $b1g1 += $price;
                }
            }
        }
        $afterOffer = $subtotal - $b1g1;

        $couponDiscount = $coupon ? $coupon->discountFor($afterOffer) : 0;
        $payable = $afterOffer - $couponDiscount;
        $shipping = $itemCount === 0 || $payable >= (float) Setting::get('free_shipping_min') ? 0 : (float) Setting::get('shipping_fee');

        return [
            'itemCount' => $itemCount,
            'mrpTotal' => $mrpTotal,
            'subtotal' => $subtotal,
            'b1g1Discount' => $b1g1,
            'couponDiscount' => $couponDiscount,
            'shipping' => $shipping,
            'total' => $payable + $shipping,
        ];
    }
}
