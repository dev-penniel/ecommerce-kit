<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            [x-cloak] {
                display: none !important;
            }

            @keyframes toast-progress {
                from {
                    transform: scaleX(1);
                }

                to {
                    transform: scaleX(0);
                }
            }

            .animate-toast-progress {
                animation: toast-progress 3s linear forwards;
            }
        </style>

         @livewireStyles
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900" x-data="{ cartOpen: false }">

            <!-- Global Cart Toast -->
            <div
                x-data="{
                    toast: false,
                    productName: '',
                    timer: null,

                    showToast(name) {
                        this.productName = name;
                        this.toast = true;

                        clearTimeout(this.timer);

                        this.timer = setTimeout(() => {
                            this.toast = false;
                        }, 3000);
                    }
                }"
                @cart-item-added.window="showToast($event.detail.productName)"
                class="pointer-events-none"
            >
                <div
                    x-cloak
                    x-show="toast"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="translate-x-8 scale-95 opacity-0"
                    x-transition:enter-end="translate-x-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="translate-x-0 scale-100 opacity-100"
                    x-transition:leave-end="translate-x-8 scale-95 opacity-0"
                    class="pointer-events-auto fixed right-5 top-5 z-[9999] w-[calc(100%-2.5rem)] max-w-sm"
                >
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white/95 p-4 shadow-2xl shadow-zinc-950/15 backdrop-blur-xl">

                        <div class="flex items-center gap-3">

                            <!-- Success Icon -->
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </div>

                            <!-- Text -->
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-zinc-950">
                                    Added to cart
                                </p>

                                <p
                                    class="mt-0.5 truncate text-xs text-zinc-500"
                                    x-text="productName"
                                ></p>
                            </div>

                            <!-- Close -->
                            <button
                                type="button"
                                @click="toast = false"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-950"
                            >
                                ×
                            </button>
                        </div>

                        <!-- Progress -->
                        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-zinc-100">
                            <div
                                class="h-full origin-left bg-emerald-500"
                                :class="toast ? 'animate-toast-progress' : ''"
                            ></div>
                        </div>

                    </div>
                </div>
            </div>


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
                                <svg
                                    class="h-5 w-5 transition-transform duration-300 group-hover:-rotate-6"
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

                                <livewire:components.cart-count />
                            </button>
                        </div>
                    </div>
                </div>
            </header>


        {{ $slot }}


        <livewire:components.cart-drawer />

        @livewireScripts

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
    </body>
</html> 