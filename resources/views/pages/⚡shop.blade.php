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
    
};
?>

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
                            wire:click="addToCart({{ $product->id }})"
                            x-data="{ added: false }"
                            @click="added = true; setTimeout(() => added = false, 1200)"
                            class="absolute bottom-4 right-4 flex h-12 w-12 translate-y-3 items-center justify-center rounded-full bg-white text-zinc-950 opacity-0 shadow-xl transition-all duration-300 hover:scale-110 group-hover:translate-y-0 group-hover:opacity-100"
                            :class="added ? 'scale-110 bg-emerald-500 text-white' : 'bg-white text-zinc-950'"
                        >
                            <span
                                x-show="!added"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="scale-0 opacity-0"
                                x-transition:enter-end="scale-100 opacity-100"
                            >
                                +
                            </span>

                            <span
                                x-show="added"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="scale-0 opacity-0"
                                x-transition:enter-end="scale-100 opacity-100"
                                class="text-lg font-bold"
                            >
                                ✓
                            </span>
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
    