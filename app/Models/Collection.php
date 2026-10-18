<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['title', 'handle', 'blurb', 'image', 'rule', 'menu_group', 'sort_order', 'is_active'])]
class Collection extends Model
{
    public const RULES = [
        'manual' => 'Manually selected products',
        'all' => 'All products',
        'new' => 'Products marked "New Launch"',
        'bestseller' => 'Products marked "Best Seller"',
    ];

    public const MENU_GROUPS = ['style' => 'Shop by Style', 'finish' => 'Shop by Finish'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('title');
    }

    /** Active products in this collection, honouring the automatic rule. */
    public function productQuery(): Builder
    {
        $q = Product::query()->active();

        return match ($this->rule) {
            'all' => $q,
            'new' => $q->where('is_new', true),
            'bestseller' => $q->where('is_bestseller', true),
            default => $q->whereHas('collections', fn ($c) => $c->whereKey($this->id)),
        };
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image);
    }
}
