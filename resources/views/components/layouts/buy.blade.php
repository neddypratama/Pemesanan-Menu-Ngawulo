<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    {{-- TinyMCE --}}
    <script src="https://cdn.tiny.cloud/1/zj7w29mcgsahkxloyg71v6365yxaoa4ey1ur6l45pnb63v42/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>

    {{--  Currency  --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js">
    </script>

    {{-- Chart.js  --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    <x-nav sticky class="px-2">

        <x-slot:brand>
            {{-- BRAND Responsive --}}
            <x-app-brand responsive />
        </x-slot:brand>

        {{-- Right side actions --}}

        <x-slot:actions class="flex justify-end gap-2">
            <x-dropdown class="relative">
                <x-slot:trigger>
                    @livewire('cart-dropdown')
                </x-slot:trigger>
            </x-dropdown>

            @if (!auth()->check())
                <x-button label="Login" icon="o-user" link="###" class="btn-ghost btn-sm ml-auto" responsive />
            @else
                <x-dropdown class="ml-auto">
                    <x-slot:trigger>
                        <x-button label="{{ auth()->user()->name }}" icon="o-user" class="btn-ghost btn-sm"
                            responsive />
                    </x-slot:trigger>
                    <x-menu-item title="Riwayat Transaksi" icon="o-gift" link="/my-orders"/>

                    <x-menu-separator />
                    <x-menu-item @click.stop="">
                        <x-theme-toggle label="Theme Toggle" responsive />
                    </x-menu-item>

                    <x-menu-separator />
                    <x-menu-item title="Logout" icon="o-power" link="/logout" />
                </x-dropdown>
            @endif
        </x-slot:actions>

    </x-nav>
    <x-main>
        <x-slot:content>
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

            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                    class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 shadow-lg transition-opacity duration-500 ease-out"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-5"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-x-5" role="alert" aria-live="assertive">

                    <x-icon name="o-x-circle" class="w-5 h-5 me-2" />
                    <span class="font-medium flex-1">{{ session('error') }}</span>

                    <button @click="show = false"
                        class="ml-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                        <x-icon name="o-x" class="w-5 h-5" />
                    </button>
                </div>
            @endif

            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Spotlight --}}
    <x-spotlight />
</body>

</html>
