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
                    <x-menu-item title="Riwayat Transaksi" icon="o-gift" link="/my-orders" />

                    <x-menu-separator />
                    <x-menu-item>
                        <x-theme-toggle label="Theme Toggle" responsive />
                    </x-menu-item>

                    <x-menu-separator />
                    <!-- Form tersembunyi -->
                    <form id="logout-form" action="/logout" method="POST" class="hidden">
                        @csrf
                    </form>

                    <!-- Link Logout -->
                    <x-menu-item title="Logout" icon="o-power" link="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();" />
                </x-dropdown>
            @endif
        </x-slot:actions>

    </x-nav>
    <x-main>
        <x-slot:content>
            <!-- ... -->

            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Spotlight --}}
    <x-spotlight />
</body>

</html>
