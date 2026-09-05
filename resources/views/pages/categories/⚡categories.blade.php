<?php

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Categories;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;


new class extends Component {

    use WithPagination;
    
    // $original name = name from database
    public $name, $originalName, $editedName, $slug, $id, $categoryId, $deleteName;
    public $search = '';

    #[computed]
    public function categories()
    {
        return Categories::when($this->search, function ($query){
            $query->where('name', 'like', '%'.$this->search.'%');
        })->latest()->paginate(50);
    }
    

    public function createCategory()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
        ]);

        // Generate slug from model name
        $this->slug = Str::slug($this->name);

        Categories::create([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);

        $this->reset();

        $this->dispatch('category-created');
    }

    public function edit($id)
    {
        $category = Categories::findOrFail($id);

        $this->id = $category->id;
        $this->editedName = $category->name;
        $this->originalName = $category->name;

    }

    public function updateCategory($id)
    {
        $validated = $this->validate([
            'editedName' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
        ]);

        $category = Categories::findOrFail($id);

        $slug = Str::slug($this->editedName);

        $category->update([
            'name' => $validated['editedName'],
            'slug' => $slug,
        ]);

        $this->originalName = $validated['editedName'];

        $this->dispatch('category-updated');

    }


    public function delete($id)
    {
        $category = Categories::findOrFail($id);
        $category->delete();
        $this->dispatch('category-deleted', $id);
        $this->modal('delete-category')->close();
    }


    public function confirmDelete($id)
    {
        $category = Categories::findOrFail($id);
        $this->categoryId = $category->id;
        $this->deleteName = $category->name;
        $this->modal('delete-category')->show();
    }

}; ?>

<div>

    {{-- confirm delete modal --}}
    <flux:modal name="delete-category" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete {{ $this->deleteName }} </flux:heading>
                <flux:text class="mt-2">
                    You're about to delete this record.<br>
                    This action cannot be reversed.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="delete({{ $categoryId }})">Delete Contact</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Create category modal --}}
    <flux:modal name="create-category" class="md:w-96">
        <form wire:submit="createCategory">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add New Category</flux:heading>
                    <flux:text class="mt-2">Create a new category entry</flux:text>
                </div>
    
                <flux:input wire:model="name" wire:model.live="slug" label="Name" placeholder="Slug is generated automaticaly" />
    
                <div class="flex">
                    <flux:spacer />
     
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                        </div>

                        <div
                            x-data="{ show: false }"
                            x-on:category-created.window="show = true; setTimeout(() => show = false, 3000)"
                        >
                            <span x-show="show" x-transition>
                                {{ __('Saved.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Update category modal --}}
    <flux:modal name="update-category" class="md:w-96">
        <form wire:submit="updateCategory({{ $id }})">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Update Category</flux:heading>
                    <flux:text class="mt-2">{{ $this->originalName }}</flux:text>
                </div>
    
                <flux:input wire:model="editedName" wire:model.live="slug" label="Name" placeholder="Slug is generated automaticaly" />
    
                <div class="flex">
                    <flux:spacer />
     
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                        </div>

                        <div
                            x-data="{ show: false }"
                            x-on:category-updated.window="show = true; setTimeout(() => show = false, 3000)"
                        >
                            <span x-show="show" x-transition>
                                {{ __('Saved.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </flux:modal>

    <div class="relative mb-6 w-full">
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl" level="1">{{ __('Categories') }}</flux:heading>
                <flux:breadcrumbs class="mb-4 mt-2">
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">Home</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item >Categories</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <flux:modal.trigger name="create-category">
                <flux:button icon="plus" size="sm" variant="primary" class="btn-sm">New Category</flux:button>
            </flux:modal.trigger>
        </div>
        <flux:separator variant="subtle" />
    </div>
    <div>

        <div class="flex justify-between items-center mb-5">

            <div class="w-[200px]">
                <flux:input
                    wire:model.live="search"
                    type="text"
                    required
                    placeholder="Search"
                    autocomplete="current-password"
                />
            </div>
        </div>

        <div class="overflow-x-auto">

            <flux:table :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column sticky class="bg-white dark:bg-zinc-900">No.</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column>Updated</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->categories as $index => $category)

                    <flux:table.row>

                        <flux:table.cell sticky class="bg-white dark:bg-zinc-900">
                            {{ ($this->categories->currentPage() - 1) * $this->categories->perPage() + $index + 1 }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $category->name }}</flux:table.cell>

                        <flux:table.cell>{{ $category->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>{{ $category->updated_at->diffForHumans() }}</flux:table.cell>

                        <flux:table.cell sticky class="py-0 bg-white dark:bg-zinc-900">
                            <flux:dropdown align="end">

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="ellipsis-horizontal"
                                />

                                <flux:menu>

                                    <flux:modal.trigger name="update-category">

                                        <flux:menu.item
                                            icon="pencil"
                                            wire:click="edit({{ $category->id }})"
                                        >
                                            Edit
                                        </flux:menu.item>
                                    </flux:modal.trigger>


                                    <flux:menu.separator />

                                    <flux:menu.item
                                        variant="danger"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $category->id }})"
                                    >
                                        Delete
                                    </flux:menu.item>

                                </flux:menu>

                            </flux:dropdown>
                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>
                        <flux:table.cell colspan="10">

                            <div class="flex min-h-80 flex-col items-center justify-center text-center">

                                <div class="mb-4 flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="users" class="size-6 text-zinc-500" />
                                </div>

                                <flux:heading size="lg">
                                    No categories yet
                                </flux:heading>

                                <flux:text class="mt-1 max-w-sm text-center">
                                    You haven't added any categories yet. Create your first category to get started.
                                </flux:text>

                                <flux:modal.trigger name="create-category">
                                    <flux:button icon="plus" size="sm" variant="primary" class="btn-sm mt-2">New Category</flux:button>
                                </flux:modal.trigger>

                            </div>

                        </flux:table.cell>
                    </flux:table.row>

                @endforelse

                </flux:table.rows>
            </flux:table>

        </div>
        

        <div class="mt-5">

            {{ $this->categories->links() }}

        </div>

    </div>
</div>

{{-- @script
<script>
    $js('showAlert', (id) => {

        confirm('Are you sure you want to delete?');

    })
</script>
@endscript --}}