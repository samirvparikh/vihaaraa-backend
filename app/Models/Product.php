<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku', 'name', 'slug', 'category_id', 'description', 'base_metal', 'plating', 'stones', 'sizes',
    'weight', 'includes', 'price', 'mrp', 'stock', 'rating', 'reviews_count', 'shape', 'stone', 'bg',
    'image', 'is_new', 'is_bestseller', 'is_active', 'sort_order',
])]
class Product extends Model
{
    // Illustration options the storefront can draw when a product has no photo yet.
    public const SHAPES = ['necklace', 'earrings', 'bangles', 'ring', 'bracelet', 'tikka', 'brooch', 'anklet'];

    public const STONES = ['none', 'ruby', 'emerald', 'pearl', 'ad', 'kundan', 'pink', 'sapphire'];

    public const BACKDROPS = ['blush', 'beige', 'maroon', 'wine', 'stone', 'midnight'];

    protected function casts(): array
    {
        return [
            'stones' => 'array',
            'sizes' => 'array',
            'price' => 'float',
            'mrp' => 'float',
            'rating' => 'float',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Every word must match somewhere: name, SKU, plating, metal, stones, category or collection. */
    public function scopeSearch(Builder $q, string $term): Builder
    {
        foreach (preg_split('/\s+/', trim($term)) as $word) {
            $like = '%'.$word.'%';
            $q->where(fn ($w) => $w->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('plating', 'like', $like)
                ->orWhere('base_metal', 'like', $like)
                ->orWhere('stones', 'like', $like)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))
                ->orWhereHas('collections', fn ($c) => $c->where('title', 'like', $like)));
        }

        return $q;
    }

    public function discountPct(): int
    {
        return $this->mrp > 0 ? (int) round(($this->mrp - $this->price) / $this->mrp * 100) : 0;
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image);
    }
}
