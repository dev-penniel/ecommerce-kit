<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

new  
#[Layout('layouts::frontend')] 
class extends Component
{
    public $search = '';

    #[Computed]
    public function products()
    {
        $search = $this->search;

        return Product::query()
            ->where('is_active', true)
            ->when(
                $search,
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }
            )
            ->with([
                'images' => fn ($query) => $query
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order'),
                'category',
            ])
            ->latest()
            ->get();
    }


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

    public function addToCart(int $productId, int $quantity = 1): void
    {
        try {
            $product = Product::query()
                ->where('is_active', true)
                ->findOrFail($productId);

            app(CartService::class)->add(
                $product,
                $quantity
            );

            $this->dispatch(
                'cart-item-added',
                productId: $productId,
                productName: $product->name
            );

            $this->dispatch('cart-updated');

        } catch (\Throwable $e) {

            $this->dispatch(
                'cart-error',
                message: $e->getMessage()
            );
        }
    }

    public function updateCartQuantity(
        int $productId,
        int $quantity
    ): void {
        try {

            $product = Product::find($productId);

            if (! $product) {
                app(CartService::class)->remove($productId);

                $this->dispatch('cart-updated');

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
};
?>

<div
    x-data="{ cartOpen: false }"
    class="min-h-screen bg-[#fafaf9] text-zinc-900"
>
    <!-- ============================================================
         NAVIGATION
    ============================================================= -->

    <header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-[#fafaf9]/90 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">

                <!-- Brand -->
                <a href="/" class="group flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-zinc-950 text-white shadow-sm transition-transform duration-300 group-hover:rotate-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12l1 13H5L6 7Z"/>
                            <path stroke-linecap="round" stroke-width="1.8" d="M9 7a3 3 0 0 1 6 0"/>
                        </svg>
                    </div>

                    <div>
                        <span class="block text-sm font-semibold tracking-tight">Essentials</span>
                        <span class="hidden text-[11px] text-zinc-500 sm:block">Curated for everyday life</span>
                    </div>
                </a>

                <!-- Navigation -->
                <nav class="hidden items-center gap-8 md:flex">
                    <a href="#shop" class="text-sm font-medium text-zinc-900 transition hover:text-zinc-500">Shop</a>
                    <a href="#featured" class="text-sm font-medium text-zinc-500 transition hover:text-zinc-900">Featured</a>
                    <a href="#about" class="text-sm font-medium text-zinc-500 transition hover:text-zinc-900">About</a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <a href="#" class="hidden rounded-full px-4 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950 sm:block">
                        Login
                    </a>

                    <button
                        type="button"
                        @click="cartOpen = true"
                        class="group relative flex h-11 w-11 items-center justify-center rounded-full bg-zinc-950 text-white shadow-sm transition hover:scale-105 hover:shadow-lg"
                    >
                        <svg class="h-5 w-5 transition-transform duration-300 group-hover:-rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 4h2l1.5 11h10L19 7H6"/>
                            <circle cx="9" cy="19" r="1"/>
                            <circle cx="17" cy="19" r="1"/>
                        </svg>

                        <span
                            x-show="$wire.cartCount > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-zinc-950 ring-2 ring-[#fafaf9]"
                        >
                            {{ $this->cartCount }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </header>


    <!-- ============================================================
         HERO
    ============================================================= -->

    <section id="featured" class="relative overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 pb-20 pt-14 sm:px-6 sm:pt-20 lg:px-8 lg:pb-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">

                <!-- Copy -->
                <div>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-2 text-xs font-medium text-zinc-600 shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Thoughtfully selected
                    </div>

                    <h1 class="max-w-2xl text-5xl font-semibold leading-[0.95] tracking-[-0.05em] text-zinc-950 sm:text-6xl lg:text-7xl">
                        Beautiful things
                        <span class="text-zinc-400">made for living.</span>
                    </h1>

                    <p class="mt-7 max-w-xl text-base leading-7 text-zinc-500 sm:text-lg">
                        Discover a refined collection of everyday essentials,
                        designed to bring a little more beauty into the things
                        you use every day.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="#shop" class="group inline-flex items-center gap-3 rounded-full bg-zinc-950 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-zinc-950/10 transition hover:-translate-y-0.5 hover:shadow-xl">
                            Shop collection
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>
                        </a>

                        <a href="#about" class="rounded-full border border-zinc-200 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            Our story
                        </a>
                    </div>
                </div>

                <!-- Hero visual -->
                <div class="relative">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-[2rem] bg-zinc-200 shadow-2xl shadow-zinc-900/10">
                        <img
                            src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80"
                            alt="Minimal wrist watch"
                            class="h-full w-full object-cover transition duration-1000 hover:scale-105"
                        >

                        <div class="absolute inset-x-5 bottom-5 rounded-2xl border border-white/30 bg-white/80 p-4 shadow-lg backdrop-blur-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-zinc-400">Featured</p>
                                    <p class="mt-1 font-semibold">Minimal Classic Watch</p>
                                </div>

                                <span class="text-sm font-semibold">M 1,299.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -left-5 hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-xl sm:block">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m5 12 4 4L19 6"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-xs font-semibold">Carefully selected</p>
                                <p class="text-[11px] text-zinc-400">Quality first</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ============================================================
         FEATURE STRIP
    ============================================================= -->

    <section id="about" class="border-y border-zinc-200 bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-zinc-200 sm:grid-cols-4">
            <div class="px-5 py-7 text-center sm:px-6">
                <span class="text-lg">✦</span>
                <p class="mt-2 text-sm font-semibold">Curated</p>
                <p class="mt-1 text-xs text-zinc-400">Thoughtfully selected</p>
            </div>

            <div class="px-5 py-7 text-center sm:px-6">
                <span class="text-lg">✓</span>
                <p class="mt-2 text-sm font-semibold">Reliable</p>
                <p class="mt-1 text-xs text-zinc-400">Quality products</p>
            </div>

            <div class="px-5 py-7 text-center sm:px-6">
                <span class="text-lg">↗</span>
                <p class="mt-2 text-sm font-semibold">Simple</p>
                <p class="mt-1 text-xs text-zinc-400">Easy ordering</p>
            </div>

            <div class="px-5 py-7 text-center sm:px-6">
                <span class="text-lg">♡</span>
                <p class="mt-2 text-sm font-semibold">Personal</p>
                <p class="mt-1 text-xs text-zinc-400">Made for you</p>
            </div>
        </div>
    </section>


    <!-- ============================================================
         SHOP
    ============================================================= -->

    <section id="shop" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">

        <div class="mb-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-zinc-400">The collection</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] sm:text-4xl">
                    Find something you love.
                </h2>
            </div>

            <div class="relative w-full sm:w-72">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

                <input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search products..."
                    class="w-full rounded-full border border-zinc-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-4 focus:ring-zinc-950/5"
                >
            </div>
        </div>


        <!-- Products -->

        
        <div class="grid grid-cols-1 gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            @if ($this->products->isEmpty() && $this->search)

    {{-- No Search Results --}}
    <div class="col-span-full py-20">
        <div class="mx-auto max-w-md text-center">

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
                        stroke-width="1.5"
                        d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                    />
                </svg>
            </div>

            <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                No results
            </p>

            <h3 class="text-xl font-semibold tracking-tight text-zinc-950">
                Nothing matched your search
            </h3>

            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-zinc-500">
                We couldn't find any products matching
                <span class="font-medium text-zinc-700">
                    "{{ $this->search }}"
                </span>.
                Try searching for something else.
            </p>

            <button
                type="button"
                wire:click="$set('search', '')"
                class="mt-6 inline-flex items-center rounded-full bg-zinc-950 px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-zinc-800"
            >
                Clear search
            </button>

        </div>
    </div>

@elseif ($this->products->isEmpty())

    {{-- Store Empty --}}
    <div class="col-span-full py-20">
        <div class="mx-auto max-w-md text-center">

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
                        stroke-width="1.5"
                        d="M20 7.5v9a2 2 0 0 1-1 1.732l-7 4.041a2 2 0 0 1-2 0l-7-4.041A2 2 0 0 1 2 16.5v-9a2 2 0 0 1 1-1.732l7-4.041a2 2 0 0 1 2 0l7 4.041A2 2 0 0 1 20 7.5Z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="m3 7 9 5 9-5M12 12v9"
                    />
                </svg>
            </div>

            <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                Our collection
            </p>

            <h3 class="text-xl font-semibold tracking-tight text-zinc-950">
                The store is empty
            </h3>

            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-zinc-500">
                There are no products available at the moment.
                Please check back soon.
            </p>

        </div>
    </div>

@else

    {{-- Products --}}
    @foreach ($this->products as $product)
        <article class="group">
            <div class="relative aspect-[4/5] overflow-hidden rounded-[1.5rem] bg-zinc-100">

                <img
                    src="{{ $product->images->first()?->image
                        ? Storage::url($product->images->first()->image)
                        : asset('images/placeholder-product.jpg') }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                >

                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>

                @if ($product->stock > 0 && $product->stock <= 5)
                    <div class="absolute left-4 top-4 rounded-full border border-white/40 bg-white/85 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-700 backdrop-blur-md">
                        Only {{ $product->stock }} left
                    </div>
                @endif

                @if ($product->stock <= 0)
                    <div class="absolute left-4 top-4 rounded-full border border-white/40 bg-white/85 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-500 backdrop-blur-md">
                        Out of stock
                    </div>
                @endif

                <button
                    type="button"
                    @click="cartOpen = true"
                    class="absolute bottom-4 right-4 flex h-12 w-12 translate-y-3 items-center justify-center rounded-full bg-white text-zinc-950 opacity-0 shadow-xl transition-all duration-300 hover:scale-110 group-hover:translate-y-0 group-hover:opacity-100"
                >
                    +
                </button>

            </div>

            <div class="pt-5">
                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">
                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            {{ $product->category->name }}
                        </p>

                        <h3 class="truncate text-base font-semibold tracking-tight">
                            {{ $product->name }}
                        </h3>
                    </div>

                    <p class="shrink-0 text-sm font-semibold">
                        M {{ number_format($product->price, 2) }}
                    </p>

                </div>

                <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-500">
                    {{ $product->description }}
                </p>
            </div>
        </article>
    @endforeach

@endif

        </div>
    </section>


    <!-- ============================================================
         CART BACKDROP
    ============================================================= -->

    <div
        x-show="cartOpen"
        x-cloak
        x-transition.opacity
        @click="cartOpen = false"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
    ></div>


    <!-- ============================================================
         CART DRAWER
    ============================================================= -->

{{-- ============================================================
     CART DRAWER
============================================================= --}}
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


    <!-- ============================================================
         FOOTER
    ============================================================= -->

    <footer class="border-t border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm font-semibold">Essentials</p>
                    <p class="mt-1 text-xs text-zinc-400">Curated for everyday life.</p>
                </div>

                <p class="text-xs text-zinc-400">
                    © 2026 Essentials. All rights reserved.
                </p>
            </div>
        </div>
    </footer>


    <style>
        [x-cloak] {
            display: none !important;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>


<script>
    function storefront() {
        return {

            cartOpen: false,

            toast: {
                visible: false,
                title: '',
                message: '',
                type: 'success',
            },

            toastTimer: null,

            init() {

                this.$wire.on('cart-item-added', (event) => {

                    this.showToast(
                        'Added to your bag',
                        `${event.productName} is now in your shopping bag.`,
                        'success'
                    );

                    this.cartOpen = true;

                });

                this.$wire.on('cart-item-removed', () => {

                    this.showToast(
                        'Removed',
                        'The item was removed from your shopping bag.',
                        'success'
                    );

                });

                this.$wire.on('cart-cleared', () => {

                    this.showToast(
                        'Bag cleared',
                        'All items have been removed.',
                        'success'
                    );

                });

                this.$wire.on('cart-error', (event) => {

                    this.showToast(
                        'Unable to update bag',
                        event.message,
                        'error'
                    );

                });

            },

            addProduct(productId) {

                this.$wire.addToCart(productId, 1);

            },

            quickAdd(productId) {

                this.$wire.addToCart(productId, 1);

            },

            showToast(title, message, type = 'success') {

                clearTimeout(this.toastTimer);

                this.toast.title = title;
                this.toast.message = message;
                this.toast.type = type;
                this.toast.visible = true;

                this.toastTimer = setTimeout(() => {

                    this.toast.visible = false;

                }, 3200);

            },

        };
    }
</script>