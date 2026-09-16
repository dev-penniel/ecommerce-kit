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
    <body class="min-h-screen bg-white dark:bg-zinc-900">

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
        {{ $slot }}

        @livewireScripts
    </body>
</html> 