<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Server-side cart for signed-in customers. Guests keep their cart in the browser and it is
 * merged in here on login. Every endpoint returns the full cart.
 */
class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->cart($request->user());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'size' => ['nullable', 'string', 'max:20'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);
        $this->add($request->user(), $data['product_id'], $data['size'] ?? null, $data['qty'] ?? 1, strict: true);

        return $this->cart($request->user());
    }

    public function update(Request $request, CartItem $item): JsonResponse
    {
        abort_unless($item->user_id === $request->user()->id, 404);
        $qty = $request->validate(['qty' => ['required', 'integer', 'min:0']])['qty'];

        if ($qty === 0) {
            $item->delete();
        } else {
            $item->update(['qty' => min($qty, $this->maxQty($item->product))]);
        }

        return $this->cart($request->user());
    }

    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        abort_unless($item->user_id === $request->user()->id, 404);
        $item->delete();

        return $this->cart($request->user());
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return $this->cart($request->user());
    }

    /** Folds a guest's browser cart into the account cart; unavailable items are silently skipped. */
    public function merge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['array', 'max:100'],
            'lines.*.product_id' => ['required', 'integer'],
            'lines.*.size' => ['nullable', 'string', 'max:20'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
        ]);
        foreach ($data['lines'] ?? [] as $line) {
            $this->add($request->user(), $line['product_id'], $line['size'] ?? null, $line['qty'], strict: false);
        }

        return $this->cart($request->user());
    }

    private function add(User $user, int $productId, ?string $size, int $qty, bool $strict): void
    {
        $product = Product::query()->active()->find($productId);
        $error = match (true) {
            ! $product => 'This product is no longer available.',
            $product->stock < 1 => 'Sorry, this piece is out of stock.',
            $product->sizes && ! in_array($size, $product->sizes, true) => 'Please select a size.',
            default => null,
        };
        if ($error) {
            if ($strict) {
                throw ValidationException::withMessages(['product_id' => $error]);
            }

            return;
        }

        $item = CartItem::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'size' => $product->sizes ? $size : '',
        ]);
        $item->qty = min(($item->exists ? $item->qty : 0) + $qty, $this->maxQty($product));
        $item->save();
    }

    private function maxQty(Product $product): int
    {
        return max(1, min((int) Setting::get('max_qty'), $product->stock));
    }

    private function cart(User $user): JsonResponse
    {
        $items = $user->cartItems()->with(['product' => fn ($q) => $q->with(ProductResource::WITH)])->oldest()->get()
            ->filter(fn ($i) => $i->product && $i->product->is_active);

        return response()->json(['items' => $items->map(fn (CartItem $i) => [
            'key' => (string) $i->id,
            'id' => $i->product_id,
            'size' => $i->size ?: null,
            'qty' => $i->qty,
            'product' => new ProductResource($i->product),
        ])->values()]);
    }
}
