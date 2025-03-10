<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Lupa Password')] class
    // <-- The same `empty` layout
    extends Component {
    #[Rule('required|email')]
    public string $email = '';

    public function sendResetLink()
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', 'Link reset password telah dikirim ke email Anda.');
        } else {
            $this->addError('email', 'Email tidak ditemukan dalam sistem.');
        }
    }
};

?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        {{-- <x-icon name="o-envelope" class="w-6 text-blue-500" /> --}}
        <span class="font-bold text-3xl bg-gradient-to-r from-blue-500 to-green-300 bg-clip-text text-transparent">
            Reset Password
        </span>
    </div>

    <x-form wire:submit="sendResetLink">
        <x-input label="E-mail" wire:model.live="email" icon="o-envelope" inline />

        <x-slot:actions class="flex justify-between">
            <x-button label="Kembali ke Login" class="btn-secondary" link="/login" />
            <x-button label="Kirim Link Reset" type="submit" icon="o-paper-airplane" class="btn-primary"
                spinner="sendResetLink" />
        </x-slot:actions>
    </x-form>
</div>
