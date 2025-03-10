<?php

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.empty')] #[Title('Verifikasi Email')] class 
extends Component {
    public bool $emailSent = false;

    public function mount()
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
    }

    public function sendVerificationEmail()
    {
        Auth::user()->sendEmailVerificationNotification();
        $this->emailSent = true;
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if (URL::hasValidSignature($request)) {
            $user->markEmailAsVerified();
            event(new Verified($user));

            request()->session()->regenerate();

            session()->flash('success', 'Email berhasil diverifikasi!');
            return redirect()->intended('/');
        }

        return redirect()
            ->route('verification.notice')
            ->withErrors(['email' => 'Tautan tidak valid atau telah kedaluwarsa.']);
    }
};
?>

<div class="md:w-96 mx-auto mt-20">
    <div class="flex items-center gap-2 mb-6">
        {{-- <x-icon name="far.envelope" class="w-6 text-blue-500" /> --}}
        <span class="font-bold text-3xl bg-gradient-to-r from-blue-500 to-green-300 bg-clip-text text-transparent">
            Verifikasi Email
        </span>
    </div>

    <p class="text-gray-600 mb-4">
        Kami telah mengirimkan email verifikasi ke alamat Anda. Silakan periksa kotak masuk Anda.
    </p>

    <x-alert class="alert-success" icon="o-check-circle" dismissable wire:show="emailSent">
        Email verifikasi telah dikirim ulang.
    </x-alert>

    <x-form wire:submit="sendVerificationEmail">
        <x-slot:actions class="flex justify-between">
            <x-button label="Kirim Ulang Email" type="submit" icon="fas.sync" class="btn-primary"
                spinner="sendVerificationEmail" />
            <x-button label="Kembali" class="btn-secondary" link="/" />
        </x-slot:actions>
    </x-form>
</div>
