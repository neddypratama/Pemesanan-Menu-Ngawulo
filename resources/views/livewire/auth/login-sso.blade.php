<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Login')] class extends Component {
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function login()
    {
        $credentials = $this->validate();

        // Hapus URL intended
        session()->forget('url.intended');

        if (Auth::attempt($credentials)) {
            request()->session()->regenerate();

            $user = Auth::user();

            // Cek apakah model User punya trait HasApiTokens
            if (!method_exists($user, 'createToken')) {
                abort(500, 'Model User belum menggunakan HasApiTokens.');
            }

            // Buat token SSO dengan Sanctum
            $token = $user->createToken('sso_token')->plainTextToken;

            // Simpan token ke session
            session()->put('sso_token', $token);

            // Flash pesan sukses
            session()->flash('success', 'Berhasil login.');

            return redirect()->route('dashboard-sso');
        } else {
            $this->addError('email', 'Email atau password salah.');
        }
    }
};
?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('index') }}">
            <x-icon name="o-square-3-stack-3d" class="w-6 -mb-1 text-orange-500" />
            <span
                class="font-bold text-3xl me-3 bg-gradient-to-r from-red-500 to-orange-300 bg-clip-text text-transparent ">
                Ngawulo
            </span>
        </a>
    </div>

    <x-form wire:submit="login">
        <x-input label="E-mail" wire:model.defer="email" icon="o-envelope" />
        <x-password label="Password" wire:model.defer="password" icon="o-key" right />
        <x-slot:actions class="flex justify-between">
            <x-button label="Kembali" icon="fas.backward" class="btn" link="/login" />
            <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
        </x-slot:actions>
    </x-form>
</div>
