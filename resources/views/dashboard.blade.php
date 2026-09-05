<x-layouts::app :title="__('Dashboard')">

    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Header --}}
        <div>
            <flux:heading size="xl">Dashboard</flux:heading>
            <flux:text class="mt-1">
                Here's an overview of what's happening in your system.
            </flux:text>
        </div>

        {{-- Statistics --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Contacts --}}
            <flux:card class="dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text>Total Contacts</flux:text>
                        <flux:heading size="xl" class="mt-2">1,248</flux:heading>
                    </div>

                    <div class="flex size-11 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950">
                        <flux:icon name="users" class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>

                <flux:text class="mt-4 text-sm">
                    <span class="text-green-600">+12%</span>
                    from last month
                </flux:text>
            </flux:card>

            {{-- Companies --}}
            <flux:card class="dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text>Companies</flux:text>
                        <flux:heading size="xl" class="mt-2">326</flux:heading>
                    </div>

                    <div class="flex size-11 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-950">
                        <flux:icon name="building-office-2" class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>

                <flux:text class="mt-4 text-sm">
                    <span class="text-green-600">+8%</span>
                    from last month
                </flux:text>
            </flux:card>

            {{-- New Contacts --}}
            <flux:card class="dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text>New This Month</flux:text>
                        <flux:heading size="xl" class="mt-2">86</flux:heading>
                    </div>

                    <div class="flex size-11 items-center justify-center rounded-lg bg-green-50 dark:bg-green-950">
                        <flux:icon name="user-plus" class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>

                <flux:text class="mt-4 text-sm">
                    <span class="text-green-600">+18%</span>
                    from last month
                </flux:text>
            </flux:card>

            {{-- Activity --}}
            <flux:card class="dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text>Activity</flux:text>
                        <flux:heading size="xl" class="mt-2">94%</flux:heading>
                    </div>

                    <div class="flex size-11 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-950">
                        <flux:icon name="chart-bar" class="size-6 text-orange-600 dark:text-orange-400" />
                    </div>
                </div>

                <flux:text class="mt-4 text-sm">
                    System activity this month
                </flux:text>
            </flux:card>

        </div>

        {{-- Main Content --}}
        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Recent Contacts --}}
            <flux:card class="lg:col-span-2 dark:bg-zinc-900">

                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Recent Contacts</flux:heading>
                        <flux:text class="mt-1">
                            Recently added contacts.
                        </flux:text>
                    </div>

                    <flux:button
                        variant="ghost"
                        size="sm"
                        href="{{ route('contacts') }}"
                    >
                        View all
                    </flux:button>
                </div>

                <div class="mt-6 overflow-x-auto">

                    <flux:table>

                        <flux:table.columns>
                            <flux:table.column>Name</flux:table.column>
                            <flux:table.column>Email</flux:table.column>
                            <flux:table.column>Company</flux:table.column>
                            <flux:table.column>Added</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>

                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar
                                            initials="TM"
                                            size="sm"
                                        />
                                        <div>
                                            <flux:text class="font-medium">
                                                Tlhonolofatso Mothae
                                            </flux:text>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    tlhono@example.com
                                </flux:table.cell>

                                <flux:table.cell>
                                    Penniel Software
                                </flux:table.cell>

                                <flux:table.cell>
                                    2 hours ago
                                </flux:table.cell>
                            </flux:table.row>

                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar
                                            initials="KM"
                                            size="sm"
                                        />
                                        <flux:text class="font-medium">
                                            Kabelo Mokoena
                                        </flux:text>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    kabelo@example.com
                                </flux:table.cell>

                                <flux:table.cell>
                                    Mokoena Group
                                </flux:table.cell>

                                <flux:table.cell>
                                    Yesterday
                                </flux:table.cell>
                            </flux:table.row>

                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar
                                            initials="LM"
                                            size="sm"
                                        />
                                        <flux:text class="font-medium">
                                            Lerato M.
                                        </flux:text>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    lerato@example.com
                                </flux:table.cell>

                                <flux:table.cell>
                                    Creative Studio
                                </flux:table.cell>

                                <flux:table.cell>
                                    2 days ago
                                </flux:table.cell>
                            </flux:table.row>

                        </flux:table.rows>

                    </flux:table>

                </div>

            </flux:card>

            {{-- Quick Actions --}}
            <flux:card class="dark:bg-zinc-900">

                <flux:heading size="lg">Quick Actions</flux:heading>

                <flux:text class="mt-1">
                    Common tasks and shortcuts.
                </flux:text>

                <div class="mt-6 flex flex-col gap-3">

                    <flux:button
                        variant="primary"
                        icon="plus"
                        class="w-full"
                        href="{{ route('contacts') }}"
                    >
                        Add Contact
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        icon="users"
                        class="w-full"
                        href="{{ route('contacts') }}"
                    >
                        Manage Contacts
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        icon="building-office-2"
                        class="w-full"
                    >
                        View Companies
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        icon="chart-bar"
                        class="w-full"
                    >
                        View Reports
                    </flux:button>

                </div>

            </flux:card>

        </div>

        {{-- Bottom Section --}}
        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Recent Activity --}}
            <flux:card class="dark:bg-zinc-900">

                <flux:heading size="lg">Recent Activity</flux:heading>

                <div class="mt-6 space-y-5">

                    <div class="flex gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-green-50 dark:bg-green-950">
                            <flux:icon name="user-plus" class="size-5 text-green-600" />
                        </div>

                        <div>
                            <flux:text>
                                <strong>New contact added</strong>
                            </flux:text>
                            <flux:text size="sm" class="text-zinc-500">
                                Kabelo Mokoena was added to contacts.
                            </flux:text>
                            <flux:text size="sm" class="mt-1 text-zinc-400">
                                2 hours ago
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-950">
                            <flux:icon name="pencil" class="size-5 text-blue-600" />
                        </div>

                        <div>
                            <flux:text>
                                <strong>Contact updated</strong>
                            </flux:text>
                            <flux:text size="sm" class="text-zinc-500">
                                Lerato M.'s information was updated.
                            </flux:text>
                            <flux:text size="sm" class="mt-1 text-zinc-400">
                                Yesterday
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-red-50 dark:bg-red-950">
                            <flux:icon name="trash" class="size-5 text-red-600" />
                        </div>

                        <div>
                            <flux:text>
                                <strong>Contact deleted</strong>
                            </flux:text>
                            <flux:text size="sm" class="text-zinc-500">
                                A contact was removed from the system.
                            </flux:text>
                            <flux:text size="sm" class="mt-1 text-zinc-400">
                                2 days ago
                            </flux:text>
                        </div>
                    </div>

                </div>

            </flux:card>

            {{-- Getting Started --}}
            <flux:card class="dark:bg-zinc-900">

                <flux:heading size="lg">Getting Started</flux:heading>

                <flux:text class="mt-1">
                    Keep your system organized.
                </flux:text>

                <div class="mt-6 space-y-4">

                    <div class="flex items-center gap-3">
                        <div class="flex size-7 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                            <flux:icon name="check" class="size-4 text-green-600" />
                        </div>

                        <flux:text>Add your first contact</flux:text>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex size-7 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                            <flux:icon name="check" class="size-4 text-green-600" />
                        </div>

                        <flux:text>Set up your company information</flux:text>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex size-7 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700">
                            <span class="text-xs">3</span>
                        </div>

                        <flux:text>Invite your team members</flux:text>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex size-7 items-center justify-center rounded-full border border-zinc-300 dark:border-zinc-700">
                            <span class="text-xs">4</span>
                        </div>

                        <flux:text>Review your reports</flux:text>
                    </div>

                </div>

            </flux:card>

        </div>

    </div>

</x-layouts::app>
