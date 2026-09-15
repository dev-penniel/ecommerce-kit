<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

         @livewireStyles
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        {{ $slot }}

        @livewireScripts
    </body>
</html> 