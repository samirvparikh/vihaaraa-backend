<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'product_id', 'sku', 'name', 'size', 'qty', 'price', 'mrp'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return ['price' => 'float', 'mrp' => 'float'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
