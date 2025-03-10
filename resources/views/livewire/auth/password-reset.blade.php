<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Reset Password')] class
    // <-- The same `empty` layout
    extends Component {
    public string $token = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:6|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public function mount($token)
    {
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', 'Password berhasil direset!');
            return redirect()->route('login');
        }

        throw ValidationException::withMessages(['email' => [__($status)]]);
    }
};
?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        {{-- <x-icon name="o-key" class="w-6 text-green-500" /> --}}
        <span class="font-bold text-3xl bg-gradient-to-r from-green-500 to-blue-300 bg-clip-text text-transparent">
            Reset Password
        </span>
    </div>

    <x-form wire:submit="resetPassword">
        <x-input label="E-mail" wire:model.live="email" icon="o-envelope" inline />
        <x-password label="Password" wire:model.live="password" icon="o-key" inline right />
        <x-password label="Confirm Password" wire:model.live="password_confirmation" icon="o-key" inline right />

        <x-slot:actions class="flex justify-between">
            <x-button label="Login" class="btn-secondary" link="/login" />
            <x-button label="Reset Password" type="submit" icon="o-paper-airplane" class="btn-primary"
                spinner="resetPassword" />
        </x-slot:actions>
    </x-form>
</div>
