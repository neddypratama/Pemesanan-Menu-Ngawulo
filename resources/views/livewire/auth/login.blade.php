<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Login')] class
    // <-- Here is the `empty` layout
    extends Component {
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->role_id == 4) {
                return redirect('/');
            } else {
                return redirect('/dashboard');
            }
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        // Hapus intended url yang mungkin tersimpan
        session()->forget('url.intended');

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();

            session()->flash('success', 'Selamat Anda berhasil login!');

            $user = auth()->user();

            if ($user->role_id == 4) {
                return redirect('/');
            } else {
                return redirect('/dashboard');
            }
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};

?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        <x-icon name="o-square-3-stack-3d" class="w-6 -mb-1 text-orange-500" />
        <span class="font-bold text-3xl me-3 bg-gradient-to-r from-red-500 to-orange-300 bg-clip-text text-transparent ">
            Ngawulo
        </span>
    </div>

    <x-form wire:submit="login">
        <x-input label="E-mail" wire:model="email" icon="o-envelope" inline />
        <x-password label="Password" wire:model="password" icon="o-key" inline right />
        <div class="flex justify-end">
            <x-button label="Lupa Password" class="" link="/forgot-password" />
        </div>
        <x-slot:actions class="flex justify-between">
            <x-button label="Create an account" class="btn-secondary" link="/register" />
            <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
        </x-slot:actions>
        <hr>
        <a href="{{ route('google-redirect') }}"
            class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded hover:bg-yellow-600">
            <i class="fa-brands fa-google mr-2"></i>
            Login dengan Google
        </a>
        <hr>
        <a href="{{ route('login-sso') }}"
            class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-red-500 rounded hover:bg-red-600">
            <i class="fa-brands fa-google mr-2"></i>
            Login SSO
        </a>
    </x-form>
</div>
