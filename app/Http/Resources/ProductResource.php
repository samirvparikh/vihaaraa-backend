<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Field names follow the storefront's original product objects (camelCase, `tags`, `reviews`…)
 * so React components keep working unchanged.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $image = $this->imageUrl();
        $gallery = $this->relationLoaded('images') ? $this->images->map->url()->all() : [];
        if ($image) {
            array_unshift($gallery, $image);
        }

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->category?->slug,
            'categoryName' => $this->category?->name,
            'collections' => $this->relationLoaded('collections') ? $this->collections->pluck('handle')->all() : [],
            'baseMetal' => $this->base_metal,
            'plating' => $this->plating ?? '',
            'stones' => $this->stones ?? [],
            'sizes' => $this->sizes ?: null,
            'weight' => $this->weight,
            'includes' => $this->includes,
            'description' => $this->description,
            'price' => $this->price,
            'mrp' => $this->mrp,
            'rating' => $this->rating,
            'reviews' => $this->reviews_count,
            'tags' => array_values(array_filter([$this->is_new ? 'new' : null, $this->is_bestseller ? 'bestseller' : null])),
            'stock' => $this->stock,
            'inStock' => $this->stock > 0,
            'shape' => $this->shape,
            'stone' => $this->stone,
            'bg' => $this->bg,
            'image' => $image,
            'gallery' => $gallery ?: null,
        ];
    }

    /** Eager loads every listing should use. */
    public const WITH = ['category', 'collections', 'images'];
}
