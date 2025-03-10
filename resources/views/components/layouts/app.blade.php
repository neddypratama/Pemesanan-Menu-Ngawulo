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
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="p-5 pt-3" />

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if ($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                        class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff"
                                no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Home" icon="o-sparkles" link="/" />
                <x-menu-item title="Users" icon="o-users" link="/users" />
                <x-menu-item title="Roles" icon="fas.layer-group" link="/roles" />
                <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-menu-item title="Wifi" icon="o-wifi" link="####" />
                    <x-menu-item title="Archives" icon="o-archive-box" link="####" />
                </x-menu-sub>
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            @if (auth()->user() && !auth()->user()->hasVerifiedEmail())
                <x-alert icon="o-exclamation-triangle" class="alert-warning">
                    Email Anda belum terverifikasi. <strong><a href="{{ route('verification.notice') }}"
                            class="underline">Verifikasi sekarang</a>.</strong>
                </x-alert>
            @else
                @if (session('success'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                        class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 shadow-lg transition-opacity duration-500 ease-out"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-5"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-5">

                        <x-icon name="o-check-circle" class="w-5 h-5 me-2" />
                        <span class="font-medium flex-1">{{ session('success') }}</span>

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
                        x-transition:leave-end="opacity-0 translate-x-5">

                        <x-icon name="o-x-circle" class="w-5 h-5 me-2" />
                        <span class="font-medium flex-1">{{ session('error') }}</span>

                        <!-- Tombol Close -->
                        <button @click="show = false"
                            class="ml-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="o-x" class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                {{-- {{ dd(session('success'))}} --}}
                {{ $slot }}
            @endif
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Spotlight --}}
    <x-spotlight />
</body>

</html>
