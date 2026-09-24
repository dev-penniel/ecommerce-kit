<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Product;
use Livewire\Attributes\Layout;


new 
#[Layout('layouts::frontend')] 
class extends Component
{
    public Product $product;

    public int $quantity = 1;

    public int $selectedImage = 0;

    public function mount(Product $product): void
    {

        $this->product = $product->load([
            'images',
            'category',
        ]);
    }

    #[Computed]
    public function productImages()
    {
        return $this->product->images;
    }

    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if ($this->product->stock < 1) {
            $this->dispatch(
                'cart-error',
                message: 'This product is currently out of stock.'
            );

            return;
        }

        if ($this->quantity > $this->product->stock) {
            $this->quantity = $this->product->stock;
        }

        app(\App\Services\CartService::class)->add(
            $this->product,
            $this->quantity
        );

        $this->dispatch(
            'cart-item-added',
            productId: $this->product->id,
            productName: $this->product->name
        );

        $this->dispatch(
            'cart-updated'
        );

        $this->dispatch(
            'product-added-to-cart',
            productName: $this->product->name
        );
    }
};
?>

<div class="min-h-screen bg-[#fafaf9]">

    {{-- ============================================================
         BREADCRUMB
    ============================================================= --}}

    <div class="mx-auto max-w-7xl px-6 pt-8 lg:px-8">

        <nav class="flex items-center gap-2 text-xs text-zinc-400">
            <a
                href="{{ route('home') }}"
                class="transition hover:text-zinc-950"
            >
                Home
            </a>

            <span>/</span>

            @if ($product->category)
                <a
                    href="#"
                    class="transition hover:text-zinc-950"
                >
                    {{ $product->category->name }}
                </a>

                <span>/</span>
            @endif

            <span class="truncate text-zinc-600">
                {{ $product->name }}
            </span>
        </nav>

    </div>


    {{-- ============================================================
         PRODUCT
    ============================================================= --}}

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8 lg:py-16">

        <div class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">

            {{-- ====================================================
                 PRODUCT GALLERY
            ===================================================== --}}

            <div
                x-data="{ lightboxOpen: false }"
                @keydown.escape.window="lightboxOpen = false"
                @keydown.left.window="
                    if (lightboxOpen) $wire.set(
                        'selectedImage',
                        {{ $selectedImage > 0 ? $selectedImage - 1 : $this->productImages->count() - 1 }}
                    )
                "
                @keydown.right.window="
                    if (lightboxOpen) $wire.set(
                        'selectedImage',
                        {{ $selectedImage < $this->productImages->count() - 1 ? $selectedImage + 1 : 0 }}
                    )
                "
            >

                {{-- Main image --}}
                <div class="relative overflow-hidden rounded-[2rem] bg-zinc-100">

                    @if ($this->productImages->count())

                        @php
                            $currentImage = $this->productImages[$selectedImage] ?? $this->productImages->first();
                        @endphp

                        <button
                            type="button"
                            @click="lightboxOpen = true"
                            class="group relative block w-full cursor-zoom-in text-left"
                        >
                            <div class="aspect-square sm:aspect-[4/3] lg:aspect-square">
                                <img
                                    src="{{ Storage::url($currentImage->image) }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]"
                                >
                            </div>

                            {{-- Zoom indicator --}}
                            <div
                                class="absolute bottom-5 right-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-zinc-950 opacity-0 shadow-lg backdrop-blur transition duration-300 group-hover:opacity-100"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="11"
                                        cy="11"
                                        r="7"
                                        stroke-width="1.7"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-width="1.7"
                                        d="m16 16 4 4M11 8v6M8 11h6"
                                    />
                                </svg>
                            </div>
                        </button>

                    @else

                        {{-- Your existing empty image state --}}

                    @endif

                    {{-- Your existing stock badge --}}

                </div>


                {{-- Thumbnails --}}
                @if ($this->productImages->count() > 1)

                    <div class="mt-4 grid grid-cols-5 gap-3">

                        @foreach ($this->productImages as $index => $image)

                            <button
                                type="button"
                                wire:click="$set('selectedImage', {{ $index }})"
                                wire:key="product-image-{{ $image->id }}"
                                class="relative cursor-pointer overflow-hidden rounded-2xl bg-zinc-100 transition"
                                @class([
                                    'ring-2 ring-zinc-950 ring-offset-2' => $selectedImage === $index,
                                    'opacity-60 hover:opacity-100' => $selectedImage !== $index,
                                ])
                            >

                                <div class="aspect-square">

                                    <img
                                        src="{{ Storage::url($image->image) }}"
                                        alt="{{ $product->name }}"
                                        class="h-full w-full object-cover"
                                    >

                                </div>

                            </button>

                        @endforeach

                    </div>

                @endif


                {{-- Lightbox --}}
                @if ($this->productImages->count())

                    <div
                        x-cloak
                        x-show="lightboxOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm sm:p-8"
                        @click.self="lightboxOpen = false"
                    >

                        {{-- Close --}}
                        <button
                            type="button"
                            @click="lightboxOpen = false"
                            class="absolute right-5 top-5 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                            aria-label="Close image viewer"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.7"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>

                        {{-- Image counter --}}
                        <div class="absolute left-5 top-5 rounded-full bg-white/10 px-4 py-2 text-xs font-medium text-white backdrop-blur">
                            {{ $selectedImage + 1 }} / {{ $this->productImages->count() }}
                        </div>

                        {{-- Previous --}}
                        @if ($this->productImages->count() > 1)
                            <button
                                type="button"
                                wire:click="$set('selectedImage', {{ $selectedImage > 0 ? $selectedImage - 1 : $this->productImages->count() - 1 }})"
                                class="absolute left-4 top-1/2 z-20 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/20 sm:left-8"
                                aria-label="Previous image"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="m15 18-6-6 6-6"
                                    />
                                </svg>
                            </button>
                        @endif

                        {{-- Main lightbox image --}}
                        <div
                            class="relative flex h-full max-h-[85vh] w-full max-w-6xl items-center justify-center"
                        >
                            <img
                                src="{{ Storage::url($currentImage->image) }}"
                                alt="{{ $product->name }}"
                                class="max-h-[75vh] max-w-full rounded-2xl object-contain shadow-2xl"
                            >
                        </div>

                        {{-- Next --}}
                        @if ($this->productImages->count() > 1)
                            <button
                                type="button"
                                wire:click="$set('selectedImage', {{ $selectedImage < $this->productImages->count() - 1 ? $selectedImage + 1 : 0 }})"
                                class="absolute right-4 top-1/2 z-20 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/20 sm:right-8"
                                aria-label="Next image"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </button>
                        @endif

                        {{-- Bottom thumbnails --}}
                        @if ($this->productImages->count() > 1)
                            <div class="absolute bottom-5 left-1/2 flex max-w-[90vw] -translate-x-1/2 gap-2 overflow-x-auto rounded-2xl bg-black/30 p-2 backdrop-blur-xl">

                                @foreach ($this->productImages as $index => $image)

                                    <button
                                        type="button"
                                        wire:click="$set('selectedImage', {{ $index }})"
                                        wire:key="lightbox-image-{{ $image->id }}"
                                        class="h-14 w-14 shrink-0 overflow-hidden rounded-xl transition sm:h-16 sm:w-16"
                                        @class([
                                            'ring-2 ring-white ring-offset-2 ring-offset-black/20' => $selectedImage === $index,
                                            'opacity-50 hover:opacity-100' => $selectedImage !== $index,
                                        ])
                                    >
                                        <img
                                            src="{{ Storage::url($image->image) }}"
                                            alt="{{ $product->name }}"
                                            class="h-full w-full object-cover"
                                        >
                                    </button>

                                @endforeach

                            </div>
                        @endif

                    </div>

                @endif

            </div>


            {{-- ====================================================
                 PRODUCT INFORMATION
            ===================================================== --}}

            <div class="flex flex-col justify-center">

                {{-- Category --}}
                @if ($product->category)

                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-400">
                        {{ $product->category->name }}
                    </p>

                @endif


                {{-- Product name --}}
                <h1 class="mt-3 max-w-xl text-3xl font-semibold tracking-[-0.03em] text-zinc-950 sm:text-4xl lg:text-5xl">
                    {{ $product->name }}
                </h1>


                {{-- Price --}}
                <div class="mt-6 flex items-baseline gap-3">

                    <p class="text-2xl font-semibold tracking-tight text-zinc-950">
                        M {{ number_format($product->price, 2) }}
                    </p>

                </div>


                {{-- Divider --}}
                <div class="my-7 h-px bg-zinc-200"></div>


                {{-- Description --}}
                @if ($product->description)

                    <div>

                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Product details
                        </p>

                        <div class="mt-3 text-sm leading-7 text-zinc-500">
                            {!! nl2br(e($product->description)) !!}
                        </div>

                    </div>

                @endif


                {{-- Stock --}}
                <div class="mt-7 flex items-center gap-2">

                    @if ($product->stock > 0)

                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                        <span class="text-xs font-medium text-zinc-600">
                            In stock
                        </span>

                    @else

                        <span class="h-2 w-2 rounded-full bg-red-500"></span>

                        <span class="text-xs font-medium text-zinc-600">
                            Currently unavailable
                        </span>

                    @endif

                </div>


                {{-- Quantity --}}
                @if ($product->stock > 0)

                    <div class="mt-8">

                        <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Quantity
                        </p>

                        <div class="flex w-fit items-center rounded-full border border-zinc-200 bg-white p-1">

                            <button
                                type="button"
                                wire:click="decrementQuantity"
                                wire:loading.attr="disabled"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 disabled:opacity-40"
                                @disabled($quantity <= 1)
                            >
                                −
                            </button>

                            <span class="w-12 text-center text-sm font-semibold">
                                {{ $quantity }}
                            </span>

                            <button
                                type="button"
                                wire:click="incrementQuantity"
                                wire:loading.attr="disabled"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 disabled:opacity-40"
                                @disabled($quantity >= $product->stock)
                            >
                                +
                            </button>

                        </div>

                    </div>


                    {{-- Add to cart --}}
                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="group mt-5 flex w-full items-center justify-between rounded-full bg-zinc-950 px-6 py-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-zinc-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60"
                    >

                        <span wire:loading.remove class="cursor-pointer" wire:target="addToCart">
                            Add to shopping bag
                        </span>

                        <span wire:loading wire:target="addToCart">
                            Adding...
                        </span>

                        <svg
                            wire:loading.remove
                            wire:target="addToCart"
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

                    </button>


                    {{-- Purchase reassurance --}}
                    <div class="mt-5 grid grid-cols-3 divide-x divide-zinc-200 rounded-2xl border border-zinc-200 bg-white py-4">

                        <div class="px-3 text-center">
                            <p class="text-[10px] font-semibold text-zinc-700">
                                Secure
                            </p>
                            <p class="mt-1 text-[9px] text-zinc-400">
                                Checkout
                            </p>
                        </div>

                        <div class="px-3 text-center">
                            <p class="text-[10px] font-semibold text-zinc-700">
                                Quality
                            </p>
                            <p class="mt-1 text-[9px] text-zinc-400">
                                Products
                            </p>
                        </div>

                        <div class="px-3 text-center">
                            <p class="text-[10px] font-semibold text-zinc-700">
                                Support
                            </p>
                            <p class="mt-1 text-[9px] text-zinc-400">
                                Available
                            </p>
                        </div>

                    </div>

                @else

                    <button
                        type="button"
                        disabled
                        class="mt-8 w-full cursor-not-allowed rounded-full bg-zinc-200 px-6 py-4 text-sm font-semibold text-zinc-400"
                    >
                        Out of stock
                    </button>

                @endif

            </div>

        </div>

    </section>


    {{-- ============================================================
         PRODUCT INFORMATION SECTION
    ============================================================= --}}

    <section class="border-t border-zinc-200 bg-white">

        <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">

            <div class="grid gap-12 lg:grid-cols-3">

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        Details
                    </p>

                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-zinc-950">
                        Everything you need to know.
                    </h2>
                </div>

                <div class="lg:col-span-2">

                    <div class="prose prose-zinc max-w-none text-sm leading-7 text-zinc-500">

                        @if ($product->description)

                            {!! nl2br(e($product->description)) !!}

                        @else

                            <p>
                                More product information will be available soon.
                            </p>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>
