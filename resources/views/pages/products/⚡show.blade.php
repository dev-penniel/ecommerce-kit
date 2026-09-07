<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'category',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]);
    }

    public function delete(): void
    {
        $this->product->delete();

        session()->flash('success', 'Product deleted successfully.');

        $this->redirectRoute('products');
    }
};
?>

<div class="max-w-[1200px] mx-auto">
    {{-- Header --}}
    <div class="relative mb-6 w-full">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>
                <div class="flex items-center gap-2">
                    <a
                        wire:navigate
                        href="{{ route('products') }}"
                        class="text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white"
                    >
                        <flux:icon.arrow-left-circle class="size-5" />
                    </a>

                    <flux:heading size="xl" level="1">
                        {{ $product->name }}
                    </flux:heading>
                </div>

                <flux:breadcrumbs class="mt-2">
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                        Home
                    </flux:breadcrumbs.item>

                    <flux:breadcrumbs.item href="{{ route('products') }}">
                        Products
                    </flux:breadcrumbs.item>

                    <flux:breadcrumbs.item>
                        {{ $product->name }}
                    </flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>


            {{-- Actions --}}
            <div class="flex items-center gap-2">

                <flux:button
                    wire:navigate
                    href="{{ route('products.edit', $product) }}"
                    icon="pencil"
                    size="sm"
                    variant="primary"
                >
                    Edit Product
                </flux:button>

                <flux:button
                    variant="danger"
                    icon="trash"
                    x-data
                    size="sm"
                    x-on:click="$dispatch('open-delete-modal')"
                >
                    Delete
                </flux:button>

            </div>
        </div>

        <flux:separator variant="subtle" class="mt-6" />
    </div>


    {{-- Main --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left: Images --}}
        <div class="lg:col-span-2">

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

                @php
                    $primaryImage = $product->images->firstWhere('is_primary', true)
                        ?? $product->images->first();
                @endphp


                {{-- Main Image --}}
                <div
    x-data="{
        mainImage: '{{ $primaryImage ? Storage::url($primaryImage->image) : '' }}',
        modalOpen: false,
    }"
    class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
>
    {{-- Main Image --}}
    <div
        class="group relative cursor-zoom-in overflow-hidden bg-zinc-100 dark:bg-zinc-800"
        @click="modalOpen = true"
    >
        @if ($primaryImage)
            <img
                :src="mainImage"
                alt="{{ $product->name }}"
                class="h-100 w-full object-contain transition duration-300 group-hover:scale-105"
            >

            {{-- Zoom indicator --}}
            <div
                class="absolute bottom-4 right-4 rounded-lg bg-black/60 p-2 text-white opacity-0 transition group-hover:opacity-100"
            >
                <flux:icon name="magnifying-glass-plus" class="size-5" />
            </div>
        @else
            <div class="flex h-100 items-center justify-center">
                <flux:icon
                    name="photo"
                    class="size-20 text-zinc-400"
                />
            </div>
        @endif
    </div>


    {{-- Image Gallery --}}
    @if ($product->images->count() > 1)
        <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex gap-3 overflow-x-auto">
                @foreach ($product->images as $image)
                    <button
                        type="button"
                        @click="mainImage = '{{ Storage::url($image->image) }}'"
                        class="size-20 shrink-0 overflow-hidden rounded-lg border-2 bg-zinc-100 transition hover:opacity-80 dark:bg-zinc-800
                            {{ $image->is_primary
                                ? 'border-zinc-900 dark:border-white'
                                : 'border-zinc-200 dark:border-zinc-700' }}"
                    >
                        <img
                            src="{{ Storage::url($image->image) }}"
                            alt="{{ $product->name }}"
                            class="h-full w-full object-cover"
                        >
                    </button>
                @endforeach
            </div>
        </div>
    @endif


    {{-- Image Modal --}}
    <div
        x-cloak
        x-show="modalOpen"
        x-transition.opacity
        @keydown.escape.window="modalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
        @click.self="modalOpen = false"
    >

        {{-- Close --}}
        <button
            type="button"
            @click="modalOpen = false"
            class="absolute right-5 top-5 z-10 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20"
        >
            <flux:icon name="x-mark" class="size-6" />
        </button>


        {{-- Modal Content --}}
        <div class="flex max-h-[90vh] w-full max-w-6xl flex-col gap-4">

            {{-- Large Image --}}
            <div class="flex flex-1 items-center justify-center">
                <img
                    :src="mainImage"
                    alt="{{ $product->name }}"
                    class="max-h-[70vh] max-w-full rounded-lg object-contain"
                >
            </div>


            {{-- Modal Gallery --}}
            <div class="flex justify-center">
                <div class="flex max-w-full gap-3 overflow-x-auto rounded-xl bg-black/40 p-3">
                    @foreach ($product->images as $image)
                        <button
                            type="button"
                            @click="mainImage = '{{ Storage::url($image->image) }}'"
                            class="size-20 shrink-0 overflow-hidden rounded-lg border-2 border-white/30 transition hover:border-white"
                        >
                            <img
                                src="{{ Storage::url($image->image) }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover"
                            >
                        </button>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

            </div>


            {{-- Description --}}
            <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

                <flux:heading size="lg">
                    Product Description
                </flux:heading>

                <div class="mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-300">

                    @if ($product->description)
                        {!! nl2br(e($product->description)) !!}
                    @else
                        <span class="italic text-zinc-400">
                            No description provided.
                        </span>
                    @endif

                </div>

            </div>

        </div>


        {{-- Right: Product Details --}}
        <div class="space-y-6">

            {{-- Overview --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <flux:heading size="lg">
                            {{ $product->name }}
                        </flux:heading>

                        @if ($product->category)
                            <flux:subheading class="mt-1">
                                {{ $product->category->name }}
                            </flux:subheading>
                        @endif
                    </div>


                    {{-- Status --}}
                    @if ($product->is_active)
                        <flux:badge color="green">
                            Active
                        </flux:badge>
                    @else
                        <flux:badge color="zinc">
                            Inactive
                        </flux:badge>
                    @endif

                </div>


                {{-- Price --}}
                <div class="mt-6">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        Price
                    </div>

                    <div class="mt-1 text-3xl font-semibold tracking-tight">
                        M{{ number_format($product->price, 2) }}
                    </div>
                </div>


                {{-- Stock --}}
                <div class="mt-6 border-t border-zinc-100 pt-5 dark:border-zinc-800">

                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <div class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon
                                    name="archive-box"
                                    class="size-5 text-zinc-500"
                                />
                            </div>

                            <div>
                                <div class="text-sm font-medium">
                                    Inventory
                                </div>

                                <div class="text-xs text-zinc-500">
                                    Available stock
                                </div>
                            </div>

                        </div>


                        <div class="text-right">

                            <div class="text-lg font-semibold">
                                {{ $product->stock }}
                            </div>

                            @if ($product->stock <= 0)
                                <div class="text-xs font-medium text-red-500">
                                    Out of stock
                                </div>
                            @elseif ($product->stock <= 5)
                                <div class="text-xs font-medium text-yellow-500">
                                    Low stock
                                </div>
                            @else
                                <div class="text-xs text-zinc-500">
                                    In stock
                                </div>
                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- Product Information --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

                <flux:heading size="lg">
                    Product Information
                </flux:heading>

                <div class="mt-5 divide-y divide-zinc-100 dark:divide-zinc-800">

                    <div class="flex justify-between gap-4 py-3 first:pt-0">
                        <span class="text-sm text-zinc-500">
                            Product ID
                        </span>

                        <span class="text-sm font-medium">
                            #{{ $product->id }}
                        </span>
                    </div>


                    <div class="flex justify-between gap-4 py-3">
                        <span class="text-sm text-zinc-500">
                            Category
                        </span>

                        <span class="text-sm font-medium">
                            {{ $product->category?->name ?? 'Uncategorized' }}
                        </span>
                    </div>


                    <div class="flex justify-between gap-4 py-3">
                        <span class="text-sm text-zinc-500">
                            Images
                        </span>

                        <span class="text-sm font-medium">
                            {{ $product->images->count() }}
                        </span>
                    </div>


                    <div class="flex justify-between gap-4 py-3">
                        <span class="text-sm text-zinc-500">
                            Created
                        </span>

                        <span class="text-sm font-medium">
                            {{ $product->created_at->format('d M Y') }}
                        </span>
                    </div>


                    <div class="flex justify-between gap-4 py-3 last:pb-0">
                        <span class="text-sm text-zinc-500">
                            Last updated
                        </span>

                        <span class="text-sm font-medium">
                            {{ $product->updated_at->format('d M Y') }}
                        </span>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Delete Confirmation --}}
    <div
        x-data="{ open: false }"
        x-on:open-delete-modal.window="open = true"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >

        {{-- Backdrop --}}
        <div
            x-on:click="open = false"
            class="absolute inset-0 bg-black/50"
        ></div>


        {{-- Modal --}}
        <div
            x-show="open"
            x-transition
            class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
        >

            <div class="flex items-start gap-4">

                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950">
                    <flux:icon
                        name="exclamation-triangle"
                        class="size-5 text-red-600"
                    />
                </div>

                <div>
                    <flux:heading size="lg">
                        Delete product?
                    </flux:heading>

                    <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        You're about to permanently delete
                        <strong class="text-zinc-800 dark:text-zinc-200">
                            {{ $product->name }}
                        </strong>.
                        This action cannot be undone.
                    </p>
                </div>

            </div>


            <div class="mt-6 flex justify-end gap-2">

                <flux:button
                    variant="subtle"
                    x-on:click="open = false"
                >
                    Cancel
                </flux:button>

                <flux:button
                    variant="danger"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="delete">
                        Delete Product
                    </span>

                    <span wire:loading wire:target="delete">
                        Deleting...
                    </span>
                </flux:button>

            </div>

        </div>

    </div>

</div>