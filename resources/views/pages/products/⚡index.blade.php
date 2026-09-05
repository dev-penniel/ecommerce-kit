<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public $search = '';

    public function with(): array
    {
        return [
            'products' => Product::query()
                ->with([
                    'category',
                    'images' => fn ($query) => $query
                        ->where('is_primary', true)
                        ->limit(1),
                ])
                ->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhereHas('category', function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->latest()
                ->get(),
        ];
    }
};
?>

<div>
    {{-- Header --}}
    <div class="relative mb-6 w-full">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">
                    {{ __('Products') }}
                </flux:heading>

                <flux:subheading class="mt-1">
                    Manage your store products and inventory.
                </flux:subheading>
            </div>

            <flux:button
                variant="primary"
                icon="plus"
                wire:navigate
                href="{{ route('products.create') }}"
            >
                Add Product
            </flux:button>
        </div>

        <flux:separator variant="subtle" class="mt-6" />
    </div>


    {{-- Search --}}
    <div class="mb-6">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Search products..."
            clearable
        />
    </div>


    {{-- Products --}}
    @if ($products->count())
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            @foreach ($products as $product)
                <div
                    wire:key="product-{{ $product->id }}"
                    class="group overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition  hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                >

                    {{-- Image --}}
                    <div class="relative h-40 w-full aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-800">

                        @if ($product->images->first())
                            <img
                                src="{{ Storage::url($product->images->first()->image) }}"
                                alt="{{ $product->name }}"
                                class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @else
                            <div class="flex h-40 w-full items-center justify-center">
                                <flux:icon name="photo" class="size-12 text-zinc-400" />
                            </div>
                        @endif

                        {{-- Status --}}
                        <div class="absolute right-3 top-3">
                            @if ($product->is_active)
                                <flux:badge color="green" size="sm">
                                    Active
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">
                                    Inactive
                                </flux:badge>
                            @endif
                        </div>
                    </div>


                    {{-- Content --}}
                    <div class="p-4">

                        {{-- Category --}}
                        @if ($product->category)
                            <div class="mb-1 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                {{ $product->category->name }}
                            </div>
                        @endif

                        {{-- Product name --}}
                        <flux:heading
                            size="lg"
                            class="truncate"
                        >
                            {{ $product->name }}
                        </flux:heading>


                        {{-- Price --}}
                        <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                            M{{ number_format($product->price, 2) }}
                        </div>


                        {{-- Stock --}}
                        <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">

                            <div class="flex items-center gap-2 text-sm">
                                <flux:icon
                                    name="archive-box"
                                    class="size-4 text-zinc-400"
                                />

                                <span class="text-zinc-600 dark:text-zinc-300">
                                    {{ $product->stock }} in stock
                                </span>
                            </div>

                            @if ($product->stock <= 0)
                                <flux:badge color="red" size="sm">
                                    Out of stock
                                </flux:badge>
                            @elseif ($product->stock <= 5)
                                <flux:badge color="yellow" size="sm">
                                    Low stock
                                </flux:badge>
                            @endif

                        </div>


                        {{-- Actions --}}
                        <div class="mt-4 flex gap-2">

                            <flux:button
                                class="flex-1"
                                size="sm"
                                variant="subtle"
                                icon="eye"
                                wire:navigate
                                href="#"
                            >
                                View
                            </flux:button>

                            <flux:button
                                size="sm"
                                variant="subtle"
                                icon="pencil"
                                wire:navigate
                                href="#"
                            >
                            </flux:button>

                        </div>

                    </div>
                </div>
            @endforeach

        </div>
    @else

        {{-- Empty state --}}
        <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-16 text-center dark:border-zinc-700">

            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="cube" class="size-6 text-zinc-500" />
            </div>

            <flux:heading size="lg" class="mt-4">
                No products found
            </flux:heading>

            <flux:subheading class="mx-auto mt-1 max-w-sm">
                {{ $search
                    ? 'Try adjusting your search.'
                    : 'Start adding products to your store.' }}
            </flux:subheading>

            @if (!$search)
                <div class="mt-5">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:navigate
                        href="#"
                    >
                        Add Product
                    </flux:button>
                </div>
            @endif

        </div>

    @endif
</div>