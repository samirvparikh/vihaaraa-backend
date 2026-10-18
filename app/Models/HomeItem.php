<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['section', 'label', 'link', 'line1', 'line2', 'italic_first', 'bg', 'image', 'product_id', 'sort_order', 'is_active'])]
class HomeItem extends Model
{
    public const SECTIONS = [
        'circles' => 'Category Circles',
        'handpicked' => 'Handpicked For You',
        'look' => 'Shop The Look',
    ];

    protected function casts(): array
    {
        return ['italic_first' => 'boolean', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
