<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->wishlist($request->user());
    }

    public function toggle(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();
        $existing = $user->wishlistItems()->where('product_id', $productId)->first();
        if ($existing) {
            $existing->delete();
        } else {
            $product = Product::query()->active()->findOrFail($productId);
            $user->wishlistItems()->create(['product_id' => $product->id]);
        }

        return $this->wishlist($user);
    }

    public function merge(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => ['array', 'max:200'], 'ids.*' => ['integer']])['ids'] ?? [];
        $user = $request->user();
        foreach (Product::query()->active()->whereIn('id', $ids)->pluck('id') as $id) {
            $user->wishlistItems()->firstOrCreate(['product_id' => $id]);
        }

        return $this->wishlist($user);
    }

    private function wishlist(User $user): JsonResponse
    {
        $products = Product::query()->active()->with(ProductResource::WITH)
            ->whereIn('id', $user->wishlistItems()->select('product_id'))->get();

        return response()->json(['ids' => $products->pluck('id'), 'products' => ProductResource::collection($products)]);
    }
}
