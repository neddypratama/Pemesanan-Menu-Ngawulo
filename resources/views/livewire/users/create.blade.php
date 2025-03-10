<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Country;
use App\Models\Language;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

new class extends Component {
    // We will use it later
    use Toast, WithFileUploads;

    // Component parameter
    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|min:8|confirmed')]
    public string $password = '';

    #[Rule('required|min:8')]
    public string $password_confirmation = '';

    #[Rule('required|sometimes')]
    public ?int $country_id = null;

    #[Rule('nullable|image|max:1024')]
    public $photo;

    public string $avatar = '';

    #[Rule('required')]
    public array $my_languages = [];

    #[Rule('sometimes')]
    public ?string $bio = null;

    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        $data['password'] = Hash::make($data['password']);

        // Upload file and save the avatar `url` on User model
        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $data['avatar'] = "/storage/$url";
        }

        // Create
        $user = User::create($data);

        // You can toast and redirect to any route
        $this->success('User berhasil dibuat!', redirectTo: '/users');
    }
};

?>

<div>
    <x-header title="Create" separator />

    <x-form wire:submit="save">
        {{--  Basic section  --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic" subtitle="Basic info from user" size="text-2xl" />
            </div>

            <div class="col-span-3 grid gap-3">
                <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
                </x-file>
                <x-input label="Name" wire:model="name" />
                <x-input label="Email" wire:model="email" />
                <x-password label="Password" wire:model="password" right />
                <x-password label="Password Confirmation" wire:model="password_confirmation" right />
                <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />
            </div>
        </div>

        {{--  Details section --}}
        <hr class="my-5" />

        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Details" subtitle="More about the user" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                {{-- Multi selection --}}
                <x-choices-offline label="My languages" wire:model="my_languages" :options="$languages" searchable />
                <x-editor wire:model="bio" label="Bio" hint="The great biography" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="/users" />
            {{-- The important thing here is `type="submit"` --}}
            {{-- The spinner property is nice! --}}
            <x-button label="Create" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>

    </x-form>
</div>
