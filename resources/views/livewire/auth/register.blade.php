<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads; 

new #[Layout('components.layouts.empty')] #[Title('Login')] class
    // <-- The same `empty` layout
    extends Component {
    
        use WithFileUploads;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    #[Rule('nullable|image|max:1024')]
    public $photo;

    public string $avatar = '';
    public int $role_id = 4;

    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function register()
    {
        $data = $this->validate();

        $data['role_id'] = $this->role_id;

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $data['avatar'] = "/storage/$url";
        }
        
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        auth()->login($user);
        request()->session()->regenerate();
        session()->flash('success', 'Selamat akun Anda berhasil dibuat!');

        return redirect()->intended('/');
    }
};

?>

<div class="md:w-96 mx-auto mt-20">
    <x-form wire:submit="register">
        <div class="mb-10 flex items-center justify-center">
            <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg">
                <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
            </x-file>
        </div>

        <x-input label="Name" wire:model="name" icon="o-user" inline />
        <x-input label="E-mail" wire:model="email" icon="o-envelope" inline />
        <x-password label="Password" wire:model="password" icon="o-key" inline right />
        <x-password label="Confirm Password" wire:model="password_confirmation" icon="o-key" inline right />

        <x-slot:actions class="flex justify-between">
            <x-button label="Already registered?" class="btn-ghost" link="/login" />
            <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
        </x-slot:actions>
    </x-form>
</div>
