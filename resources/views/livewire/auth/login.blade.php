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
            if (auth()->user()->role_id != 4) {
                return redirect('/dashboard');
            } else {
                return redirect('/');
            }
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();

            session()->flash('success', 'Selamat Anda berhasil login!');

            if (auth()->user()->role_id != 4) {
                return redirect()->intended('/dashboard');
            } else {
                return redirect()->intended('/');
            }
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};

?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        <x-icon name="o-square-3-stack-3d" class="w-6 -mb-1 text-purple-500" />
        <span
            class="font-bold text-3xl me-3 bg-gradient-to-r from-purple-500 to-pink-300 bg-clip-text text-transparent ">
            app
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
    </x-form>
</div>
