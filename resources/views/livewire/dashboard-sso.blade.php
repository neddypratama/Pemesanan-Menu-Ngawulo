<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Dashboard SSO')] class extends Component {
    //
};
?>

<div class="max-w-2xl mx-auto mt-16 space-y-6 text-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-orange-600">Dashboard SSO</h1>
        <p class="text-lg text-gray-600 mt-2">
            Selamat datang, <span class="font-semibold">{{ auth()->user()->name }}</span> 👋
        </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card class="p-6 text-center">
            <x-icon name="o-archive-box" class="w-10 h-10 mx-auto text-blue-500 mb-3" />
            <h3 class="text-xl font-semibold mb-2">Pengelolaan Stok</h3>
            <a href="http://127.0.0.1:8003/sso/callback?token={{ session('sso_token') }}"
                class="btn btn-primary w-full">
                Masuk
            </a>
        </x-card>

        <x-card class="p-6 text-center">
            <x-icon name="o-receipt-percent" class="w-10 h-10 mx-auto text-green-500 mb-3" />
            <h3 class="text-xl font-semibold mb-2">Pengelolaan Pesanan</h3>
            <a href="http://127.0.0.1:8001/sso/callback?token={{ session('sso_token') }}"
                class="btn btn-primary w-full">
                Masuk
            </a>
        </x-card>
    </div>

    <x-button label="Logout" icon="o-arrow-left-on-rectangle" link="/logout" responsive />
</div>
