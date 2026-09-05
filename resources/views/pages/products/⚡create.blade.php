<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Categories;
use App\Models\Product;

new class extends Component
{

    use WithFileUploads;

    public string $name = '';
    public ?int $category_id = null;
    public ?string $description = null;
    public $price = 0;
    public int $stock = 0;
    public bool $is_active = true;
    public array $images = [];
    public $search = '';


    #[computed]
    public function categories()
    {
        return Categories::when($this->search, function ($query){
            $query->where('name', 'like', '%'.$this->search.'%');
        })->latest()->paginate(50);
    }

    public function create()
    {
        $validated = $this->validate([

            'name' => ['required', 'max:266'],
            'description' => ['required'],
            'price' => ['required'],
            'stock' => ['required'],
            'is_active' => ['required'],

        ]);

        $slug = Str::slug($this->name);

        $product = Product::create([
            'category_id' => $this->category_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_active' => $validated['is_active']
        ]);

        // handle images
        foreach ($this->images as $index => $image) {
            $path = $image->store('products', 'public');

            $product->images()->create([
                'image' => $path,
                'is_primary' => $index === 0,
                'sort_order' => $index + 1,
            ]);
        }

        $this->dispatch('product-created');
        $this->reset();

    }
};
?>

<div>
    {{-- Header --}}
    <div class="relative mb-6 w-full">
        <div class="flex justify-between items-center">
            <div>
                <div class="flex gap-2 items-center">
                    <a wire:navigate href="{{ route('products') }}">
                        <flux:icon.arrow-left-circle />
                    </a>

                    <flux:heading size="xl" level="1">
                        {{ __('New Product') }}
                    </flux:heading>
                </div>

                <flux:breadcrumbs class="mb-4 mt-2">
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">
                        Home
                    </flux:breadcrumbs.item>

                    <flux:breadcrumbs.item href="{{ route('products') }}">
                        Products
                    </flux:breadcrumbs.item>

                    <flux:breadcrumbs.item>
                        Create
                    </flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>

        <flux:separator variant="subtle" />
    </div>


    {{-- Form --}}
    <form wire:submit="create" class="space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Information --}}
            <div class="lg:col-span-2">
                <flux:card class="space-y-6 dark:bg-zinc-900">

                    <div>
                        <flux:heading size="lg">
                            Product Information
                        </flux:heading>

                        <flux:text class="mt-1">
                            Add the basic information about your product.
                        </flux:text>
                    </div>


                    {{-- Product Name --}}
                    <flux:field>
                        <flux:label>
                            Product Name
                        </flux:label>

                        <flux:input
                            wire:model="name"
                            placeholder="e.g. Classic T-Shirt"
                        />

                        <flux:error name="name" />
                    </flux:field>


                    {{-- Category --}}
                    <flux:field>
                        <flux:label>
                            Category
                        </flux:label>

                        <flux:select wire:model="category_id">
                            <flux:select.option value="">
                                Select a category
                            </flux:select.option>

                            @foreach ($this->categories as $category)
                                <flux:select.option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:error name="category_id" />
                    </flux:field>


                    {{-- Description --}}
                    <flux:field>
                        <flux:label>
                            Description
                        </flux:label>

                        <flux:textarea
                            wire:model="description"
                            rows="6"
                            placeholder="Describe your product..."
                        />

                        <flux:error name="description" />
                    </flux:field>


                    {{-- Price + Stock --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <flux:field>
                            <flux:label>
                                Price
                            </flux:label>

                            <flux:input
                                wire:model="price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            />

                            <flux:error name="price" />
                        </flux:field>


                        <flux:field>
                            <flux:label>
                                Stock
                            </flux:label>

                            <flux:input
                                wire:model="stock"
                                type="number"
                                min="0"
                                placeholder="0"
                            />

                            <flux:error name="stock" />
                        </flux:field>

                    </div>

                </flux:card>
            </div>


            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Product Images --}}
                <flux:card class="dark:bg-zinc-900">
                    <div class="space-y-4">

                        <div>
                            <flux:heading size="lg">
                                Product Images
                            </flux:heading>

                            <flux:text class="mt-1">
                                Upload multiple images for your product.
                            </flux:text>
                        </div>


                        {{-- Image uploader --}}
                        <div
                            x-data="{
                                images: [],

                                preview(event) {
                                    this.images = Array.from(event.target.files).map(file => ({
                                        file: file,
                                        url: URL.createObjectURL(file)
                                    }))
                                },

                                remove(index) {
                                    URL.revokeObjectURL(this.images[index].url)
                                    this.images.splice(index, 1)
                                }
                            }"
                            class="space-y-4"
                        >

                            {{-- Upload Area --}}
                            <label
                                for="product-images"
                                class="flex flex-col items-center justify-center w-full min-h-40 px-6 py-8
                                       border-2 border-dashed border-zinc-300 dark:border-zinc-700
                                       rounded-xl cursor-pointer
                                       hover:border-zinc-400 dark:hover:border-zinc-600
                                       hover:bg-zinc-50 dark:hover:bg-zinc-800/50
                                       transition"
                            >

                                <div class="flex flex-col items-center text-center">

                                    <div class="flex items-center justify-center size-12 rounded-full
                                                bg-zinc-100 dark:bg-zinc-800 mb-3">
                                        <flux:icon.photo class="size-6" />
                                    </div>

                                    <flux:text class="font-medium">
                                        Click to upload images
                                    </flux:text>

                                    <flux:text size="sm" class="mt-1">
                                        PNG, JPG or WEBP
                                    </flux:text>

                                    <flux:text size="sm">
                                        You can select multiple images
                                    </flux:text>

                                </div>

                                <input
                                    id="product-images"
                                    type="file"
                                    wire:model="images"
                                    multiple
                                    accept="image/png,image/jpeg,image/webp"
                                    class="hidden"
                                    @change="preview($event)"
                                />

                            </label>


                            {{-- Uploading --}}
                            <div wire:loading wire:target="images">
                                <flux:text>
                                    Uploading images...
                                </flux:text>
                            </div>


                            {{-- Preview --}}
                            <template x-if="images.length">
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">

                                    <template
                                        x-for="(image, index) in images"
                                        :key="index"
                                    >
                                        <div class="relative group aspect-square">

                                            <img
                                                :src="image.url"
                                                class="w-full h-full object-cover rounded-lg border border-zinc-200 dark:border-zinc-700"
                                            />

                                            <button
                                                type="button"
                                                @click="remove(index)"
                                                class="absolute top-2 right-2 flex items-center justify-center
                                                       size-7 rounded-full
                                                       bg-white/90 dark:bg-zinc-900/90
                                                       shadow-sm
                                                       opacity-0 group-hover:opacity-100
                                                       transition"
                                            >
                                                <flux:icon.x-mark class="size-4" />
                                            </button>

                                        </div>
                                    </template>

                                </div>
                            </template>


                            <flux:error name="images.*" />

                        </div>

                    </div>
                </flux:card>


                {{-- Status --}}
                <flux:card class="dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4">

                        <div>
                            <flux:heading size="sm">
                                Product Status
                            </flux:heading>

                            <flux:text size="sm" class="mt-1">
                                Make this product visible in your store.
                            </flux:text>
                        </div>

                        <flux:switch
                            wire:model="is_active"
                        />

                    </div>
                </flux:card>


                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">

                    <flux:button
                        variant="ghost"
                        href="{{ route('products') }}"
                        wire:navigate
                    >
                        Cancel
                    </flux:button>

                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="save">
                            Create Product
                        </span>

                        <span wire:loading wire:target="save">
                            Creating...
                        </span>
                    </flux:button>

                </div>

            </div>

        </div>

    </form>
</div>