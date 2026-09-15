<?php

use Livewire\Component;
use App\Models\Contacts;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Notifications\SystemNotification;



new class extends Component
{

    use WithPagination;
 
    public $names, $email, $phone, $company, $job_title, $notes, $contactId, $deleteName, $contactEditNames;
    public $search = '';

    public function mount(){

    }

    public function resetForm()
    {
        $this->reset();
    }

    #[computed]
    public function contacts()
    {

        return Contacts::query()

        ->when($this->search, function ($query) {
            $query->where('names', 'like', '%' . $this->search. '%')
            ->orWhere('email', 'like', '%' . $this->search. '%')
            ->orWhere('company', 'like', '%' . $this->search. '%')
            ->orWhere('phone', 'like', '%' . $this->search. '%')
            ->orWhere('job_title', 'like', '%' . $this->search. '%');

        })->latest()->paginate(10);

    }
    
    public function createContact()
    {
        $validated = $this->validate([
            'names' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:contacts,email'],
            'phone' => ['required', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $contact = Contacts::create($validated);

        auth()->user()->notify(
            new SystemNotification(
                title: 'New Contact',
                message: 'Contact has been added',
                type: 'success',
                url: route('contacts'),
            )
        );

        $this->resetForm();

        $this->dispatch('contact-created');
    }


    public function edit($id)
    {
        $contact = Contacts::findOrFail($id);
        $this->contactEditNames = $contact->names;
        $this->contactId = $contact->id;

        $this->names = $contact->names;
        $this->email = $contact->email;
        $this->phone = $contact->phone;
        $this->company = $contact->company;
        $this->job_title = $contact->job_title;
        $this->notes = $contact->notes;
    }

    public function update($id)
    {

        $contact = Contacts::findOrFail($id);

        $validated = $this->validate([
            'names' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($contact->id)],
            'phone' => ['required', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $contact->update($validated);

        $this->dispatch('contact-updated');

    }


    public function deleteContact($id)
    {
        $contact = Contacts::findOrFail($id);
        $contact->delete();

        $this->modal('delete-contacts')->close();

        $this->dispatch('contact-deleted');

    }

    public function confirmDelete($id)
    {
        $this->contactId = $id;
        $contact = Contacts::findOrFail($id);
        $this->deleteName = $contact->names;
        $this->modal('delete-contacts')->show();
    }
};

?>

<div>

    {{-- confirm delete modal --}}
    <flux:modal name="delete-contacts" class="min-w-[22rem]">
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
                <flux:button type="submit" variant="danger" wire:click="deleteContact({{ $contactId }})">Delete Contact</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Create contacts modal --}}
    <flux:modal name="create-category" class=" md:w-full">
        <form wire:submit="createContact">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add New Contact</flux:heading>
                    <flux:text class="mt-2">Create a new contact entry</flux:text>
                </div>
    

                <div class="grid md:grid-cols-2 gap-4">

                    <div class="flex flex-col gap-2">
                        <flux:input wire:model="names" label="Names" placeholder="Names" />
                        <flux:input wire:model="email" label="Email" placeholder="Email" />
                        <flux:input wire:model="phone" label="Phone" placeholder="Phone" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <flux:input wire:model="company" label="Company" placeholder="Company" />
                        <flux:input wire:model="job_title" label="Job Title" placeholder="Job Title" />
                        <flux:textarea wire:model="notes" label="Notes" placeholder="notes" />
                    </div>

                </div>
    
                <div class="flex">
                    <flux:spacer />
     
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                        </div>

                        <div
                            x-data="{ show: false }"
                            x-on:contact-created.window="show = true; setTimeout(() => show = false, 3000)"
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

    {{-- edit contcats modal --}}
    <flux:modal name="edit-contact" class="md:w-full">
        <form wire:submit="update({{ $contactId }})">
            <div class="space-y-6">

                <div>
                    <flux:heading size="lg">Edit Contact: {{ $contactEditNames }} </flux:heading>
                    <flux:text class="mt-2">Edit this contact entry</flux:text>
                </div>
    

                <div class="grid grid-cols-2 gap-4">

                    <div class="flex flex-col gap-2">
                        <flux:input wire:model="names" label="Names" placeholder="Names" />
                        <flux:input wire:model="email" label="Email" placeholder="Email" />
                        <flux:input wire:model="phone" label="Phone" placeholder="Phone" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <flux:input wire:model="company" label="Company" placeholder="Company" />
                        <flux:input wire:model="job_title" label="Job Title" placeholder="Job Title" />
                        <flux:textarea wire:model="notes" label="Notes" placeholder="notes" />
                    </div>

                </div>
    
                <div class="flex">
                    <flux:spacer />
     
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                        </div>

                        <div
                            x-data="{ show: false }"
                            x-on:contact-updated.window="show = true; setTimeout(() => show = false, 3000)"
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
                <flux:heading size="xl" level="1">{{ __('Contacts') }}</flux:heading>
                <flux:breadcrumbs class="mb-4 mt-2">
                    <flux:breadcrumbs.item href="{{ route('dashboard') }}">Home</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item >Contacts</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            
                <flux:modal.trigger name="create-category">
                    <flux:button icon="plus" size="sm" variant="primary" class="btn-sm">New Contact</flux:button>
                </flux:modal.trigger>
                
            
        </div>

        <flux:separator variant="subtle" />
    </div>
    <div>

        <div class="flex justify-between items-center mb-5">

            <div class="w-50">
                <flux:input
                    wire:model.live="search"
                    type="text"
                    required
                    placeholder="Search"
                    autocomplete="current-password"
                />
            </div>

        </div>


        <flux:table :paginate="$this->contacts">
            <flux:table.columns>
                <flux:table.column sticky class="bg-white dark:bg-zinc-900">No:</flux:table.column>
                <flux:table.column>Names</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column>Job Title</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column>Updated</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->contacts as $index => $contact)

                    <flux:table.row>

                        <flux:table.cell sticky class="bg-white dark:bg-zinc-900">
                            {{ ($this->contacts->currentPage() - 1) * $this->contacts->perPage() + $index + 1 }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $contact->names }}</flux:table.cell>
                        <flux:table.cell>{{ $contact->email }}</flux:table.cell>
                        <flux:table.cell>{{ $contact->phone }}</flux:table.cell>
                        <flux:table.cell>{{ $contact->company }}</flux:table.cell>
                        <flux:table.cell>{{ $contact->job_title }}</flux:table.cell>

                        <flux:table.cell>{{ $contact->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>{{ $contact->updated_at->diffForHumans() }}</flux:table.cell>

                        <flux:table.cell sticky class="py-0 bg-white dark:bg-zinc-900">
                            <flux:dropdown align="end">

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="ellipsis-horizontal"
                                />

                                <flux:menu>

                                    <flux:modal.trigger name="edit-contact">

                                        <flux:menu.item
                                            icon="pencil"
                                            wire:click="edit({{ $contact->id }})"
                                        >
                                            Edit
                                        </flux:menu.item>
                                    </flux:modal.trigger>


                                    <flux:menu.separator />

                                    <flux:menu.item
                                        variant="danger"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $contact->id }})"
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
                                    No contacts yet
                                </flux:heading>

                                <flux:text class="mt-1 max-w-sm text-center">
                                    You haven't added any contacts yet. Create your first contact to get started.
                                </flux:text>

                                <div class="mt-5">
                                    <flux:button
                                        variant="primary"
                                        size="sm"
                                        icon="plus"
                                        wire:click="create"
                                    >
                                        Create Contact
                                    </flux:button>
                                </div>

                            </div>

                        </flux:table.cell>
                    </flux:table.row>

                @endforelse


                

            </flux:table.rows>
        </flux:table>
        
    </div>
</div>