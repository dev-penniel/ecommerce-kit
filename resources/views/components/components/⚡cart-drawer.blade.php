<?php

use Livewire\Component;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Product;


new class extends Component
{
    #[Computed]
    public function cart()
    {
        return app(CartService::class)->items();
    }

    #[Computed]
    public function cartCount(): int
    {
        return app(CartService::class)->count();
    }

    #[Computed]
    public function cartSubtotal(): float
    {
        return app(CartService::class)->subtotal();
    }

    public function updateCartQuantity(
        int $productId,
        int $quantity
    ): void {
        try {
            $product = Product::find($productId);

            if (! $product) {
                app(CartService::class)->remove($productId);

                return;
            }

            app(CartService::class)->update(
                $product,
                $quantity
            );

            $this->dispatch('cart-updated');

        } catch (\Throwable $e) {
            $this->dispatch(
                'cart-error',
                message: $e->getMessage()
            );
        }
    }

    public function removeFromCart(int $productId): void
    {
        app(CartService::class)->remove($productId);

        $this->dispatch(
            'cart-item-removed'
        );

        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        app(CartService::class)->clear();

        $this->dispatch('cart-cleared');
        $this->dispatch('cart-updated');
    }

    #[On('cart-updated')]
    public function refreshCart()
    {
        unset($this->cart);
        unset($this->cartCount);
        unset($this->cartSubtotal);
    }
};
?>

{{-- ============================================================
     CART DRAWER
============================================================= --}}

<div>

    <div
            x-show="cartOpen"
            x-cloak
            x-transition.opacity
            @click="cartOpen = false"
            class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
        ></div>


    <aside
        x-show="cartOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[60] flex w-full max-w-lg flex-col bg-[#fafaf9] shadow-2xl"
        @keydown.escape.window="cartOpen = false"
    >

        {{-- Cart header --}}
        <div class="flex items-center justify-between border-b border-zinc-200 bg-white px-6 py-5">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                    Your selection
                </p>

                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    Shopping bag
                </h2>
            </div>

            <button
                type="button"
                @click="cartOpen = false"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-950"
            >
                ×
            </button>
        </div>


        {{-- Cart body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6">

            @if ($this->cart->isEmpty())

                {{-- Empty cart --}}
                <div class="flex min-h-[400px] items-center justify-center">
                    <div class="max-w-sm text-center">

                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-[1.5rem] bg-zinc-100">
                            <svg
                                class="h-9 w-9 text-zinc-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.7"
                                    d="M3 4h2l1.5 11h10L19 7H6"
                                />
                                <circle cx="9" cy="19" r="1"/>
                                <circle cx="17" cy="19" r="1"/>
                            </svg>
                        </div>

                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Your selection
                        </p>

                        <h3 class="mt-2 text-xl font-semibold tracking-tight">
                            Your shopping bag is empty
                        </h3>

                        <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-zinc-500">
                            Looks like you haven't added anything yet.
                            Explore our collection and find something you love.
                        </p>

                        <button
                            type="button"
                            @click="cartOpen = false"
                            class="mt-6 rounded-full bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                        >
                            Continue shopping
                        </button>

                    </div>
                </div>

            @else

                <div class="space-y-4">

                    @foreach ($this->cart as $item)

                        <div
                            wire:key="cart-item-{{ $item['id'] }}"
                            class="group relative flex gap-4 rounded-2xl border border-zinc-200 bg-white p-3 transition hover:shadow-md"
                        >

                            {{-- Product image --}}
                            <div class="h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-zinc-100">

                                <img
                                    src="{{ $item['image']
                                        ? Storage::url($item['image'])
                                        : asset('images/placeholder-product.jpg') }}"
                                    alt="{{ $item['name'] }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                >

                            </div>


                            <div class="min-w-0 flex-1">

                                <div class="flex justify-between gap-3">

                                    <div class="min-w-0">

                                        <h3 class="truncate text-sm font-semibold">
                                            {{ $item['name'] }}
                                        </h3>

                                        <p class="mt-1 text-xs text-zinc-400">
                                            M {{ number_format($item['price'], 2) }} each
                                        </p>

                                    </div>


                                    {{-- Remove --}}
                                    <button
                                        type="button"
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-zinc-300 transition hover:bg-red-50 hover:text-red-500"
                                    >
                                        ×
                                    </button>

                                </div>


                                <div class="mt-5 flex items-center justify-between">

                                    {{-- Quantity --}}
                                    <div class="flex items-center rounded-full border border-zinc-200 p-1">

                                        <button
                                            type="button"
                                            wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                            wire:loading.attr="disabled"
                                            class="flex h-7 w-7 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-100"
                                        >
                                            −
                                        </button>

                                        <span class="w-8 text-center text-xs font-semibold">
                                            {{ $item['quantity'] }}
                                        </span>

                                        <button
                                            type="button"
                                            wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                            wire:loading.attr="disabled"
                                            class="flex h-7 w-7 items-center justify-center rounded-full text-zinc-500 hover:bg-zinc-100"
                                        >
                                            +
                                        </button>

                                    </div>


                                    {{-- Item subtotal --}}
                                    <p class="text-sm font-semibold">
                                        M {{ number_format($item['subtotal'], 2) }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- Clear cart --}}
                <button
                    type="button"
                    wire:click="clearCart"
                    wire:loading.attr="disabled"
                    class="mt-5 text-xs font-medium text-zinc-400 transition hover:text-red-500"
                >
                    Clear shopping bag
                </button>

            @endif

        </div>


        {{-- Cart footer --}}
        @if ($this->cartCount > 0)

            <div class="border-t border-zinc-200 bg-white p-6">

                <div class="mb-5 space-y-3">

                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">
                            Items
                        </span>

                        <span class="font-medium">
                            {{ $this->cartCount }}
                        </span>
                    </div>


                    <div class="flex items-end justify-between">

                        <div>
                            <p class="text-xs text-zinc-400">
                                Subtotal
                            </p>

                            <p class="mt-1 text-2xl font-semibold tracking-tight">
                                M {{ number_format($this->cartSubtotal, 2) }}
                            </p>
                        </div>

                        <p class="text-xs text-zinc-400">
                            Delivery calculated at checkout
                        </p>

                    </div>

                </div>


                <a
                    href="#"
                    class="group flex w-full items-center justify-between rounded-full bg-zinc-950 px-6 py-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-xl"
                >
                    <span>
                        Continue to checkout
                    </span>

                    <svg
                        class="h-4 w-4 transition-transform group-hover:translate-x-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.7"
                            d="M5 12h14m-6-6 6 6-6 6"
                        />
                    </svg>
                </a>

                <p class="mt-4 text-center text-[11px] text-zinc-400">
                    Secure ordering · Proof of payment supported
                </p>

            </div>

        @endif

    </aside>
</div>