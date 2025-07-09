<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Dashboard SSO')] class extends Component {
    //
};
?>

<div class="mx-auto mt-16 space-y-6 text-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-orange-600">Dashboard SSO</h1>
        <p class="text-lg text-gray-600 mt-2">
            Selamat datang, <span class="font-semibold">{{ auth()->user()->name }}</span> 👋
        </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Modul Pengelolaan Stok --}}
        <x-card class="p-6 text-center">
            <x-icon name="o-cube" class="w-10 h-10 mx-auto text-blue-500 mb-3" />
            <h3 class="text-xl font-semibold mb-2">Pengelolaan Stok</h3>
            <a href="http://127.0.0.1:8003/sso/callback?token={{ session('sso_token') }}"
                class="btn btn-primary w-full">
                Masuk
            </a>
        </x-card>

        {{-- Modul Pengelolaan Pesanan --}}
        <x-card class="p-6 text-center">
            <x-icon name="o-shopping-cart" class="w-10 h-10 mx-auto text-green-500 mb-3" />
            <h3 class="text-xl font-semibold mb-2">Pengelolaan Pesanan</h3>
            <a href="http://127.0.0.1:8001/sso/callback?token={{ session('sso_token') }}"
                class="btn btn-primary w-full">
                Masuk
            </a>
        </x-card>

        {{-- Modul Pelaporan --}}
        <x-card class="p-6 text-center">
            <x-icon name="o-chart-bar" class="w-10 h-10 mx-auto text-purple-500 mb-3" />
            <h3 class="text-xl font-semibold mb-2">Pelaporan</h3>
            <a href="http://127.0.0.1:8002/sso/callback?token={{ session('sso_token') }}"
                class="btn btn-primary w-full">
                Masuk
            </a>
        </x-card>
    </div>

    <x-button label="Logout" icon="o-arrow-left-on-rectangle" link="/logout" responsive />
</div>
