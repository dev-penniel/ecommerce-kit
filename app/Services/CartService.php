<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    /**
     * Get the raw cart.
     */
    public function raw(): array
    {
        return Session::get($this->sessionKey, []);
    }

    /**
     * Get the cart products with quantities and totals.
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        $products = Product::query()
            ->with([
                'images' => fn ($query) => $query
                    ->where('is_primary', true)
                    ->orderBy('sort_order'),
            ])
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function ($quantity, $productId) use ($products) {

                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $quantity = min(
                    max((int) $quantity, 1),
                    $product->stock
                );

                if ($quantity <= 0) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'product' => $product,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'stock' => $product->stock,
                    'subtotal' => (float) $product->price * $quantity,
                    'image' => $product->images->first()?->image,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Add a product to the cart.
     */
    public function add(Product $product, int $quantity = 1): array
    {
        if (! $product->is_active) {
            throw new \RuntimeException(
                'This product is no longer available.'
            );
        }

        if ($product->stock <= 0) {
            throw new \RuntimeException(
                'This product is currently out of stock.'
            );
        }

        $quantity = max($quantity, 1);

        $cart = $this->raw();

        $currentQuantity = (int) ($cart[$product->id] ?? 0);

        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $product->stock) {
            throw new \RuntimeException(
                "Only {$product->stock} items are available."
            );
        }

        $cart[$product->id] = $newQuantity;

        Session::put($this->sessionKey, $cart);

        return $this->summary();
    }

    /**
     * Update a product quantity.
     */
    public function update(Product $product, int $quantity): array
    {
        $cart = $this->raw();

        if (! isset($cart[$product->id])) {
            return $this->summary();
        }

        if ($quantity <= 0) {
            unset($cart[$product->id]);

            Session::put($this->sessionKey, $cart);

            return $this->summary();
        }

        if (! $product->is_active) {
            unset($cart[$product->id]);

            Session::put($this->sessionKey, $cart);

            throw new \RuntimeException(
                'This product is no longer available.'
            );
        }

        if ($quantity > $product->stock) {
            throw new \RuntimeException(
                "Only {$product->stock} items are available."
            );
        }

        $cart[$product->id] = $quantity;

        Session::put($this->sessionKey, $cart);

        return $this->summary();
    }

    /**
     * Remove a product.
     */
    public function remove(int $productId): array
    {
        $cart = $this->raw();

        unset($cart[$productId]);

        Session::put($this->sessionKey, $cart);

        return $this->summary();
    }

    /**
     * Empty the entire cart.
     */
    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    /**
     * Number of products in cart.
     */
    public function count(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Server-side subtotal.
     */
    public function subtotal(): float
    {
        return $this->items()->sum('subtotal');
    }

    /**
     * Cart summary.
     */
    public function summary(): array
    {
        return [
            'count' => $this->count(),
            'subtotal' => $this->subtotal(),
            'items' => $this->items(),
        ];
    }
}