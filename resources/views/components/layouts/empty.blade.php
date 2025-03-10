<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">
    {{-- You could elaborate the layout here --}}
    {{-- The important part is to have a different layout from the main app layout --}}
    <x-main>
        <x-slot:content>
            <div class="container mx-auto px-4">
                <div class="flex items-center">
                    <!-- ... -->
                    @if (session('status'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                            class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 shadow-lg transition-opacity duration-500 ease-out"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-x-5"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 translate-x-5">

                            <x-icon name="o-check-circle" class="w-5 h-5 me-2" />
                            <span class="font-medium flex-1">{{ session('status') }}</span>

                            <!-- Tombol Close -->
                            <button @click="show = false"
                                class="ml-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="o-x-circle" class="w-5 h-5" />
                            </button>
                        </div>
                    @endif
                    {{ $slot }}
                </div>
            </div>
        </x-slot:content>
    </x-main>
</body>

</html>
