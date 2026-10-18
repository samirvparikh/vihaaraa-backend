<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'blurb', 'image', 'sort_order', 'show_in_menu', 'is_active'])]
class Category extends Model
{
    protected function casts(): array
    {
        return ['show_in_menu' => 'boolean', 'is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image);
    }
}
