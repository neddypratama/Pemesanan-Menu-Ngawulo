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

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('nullable|image|max:1024')]
    public $photo;

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

    public function mount(): void
    {
        $this->fill($this->user);

        // Fill the selected languages property
        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Update
        $this->user->update($data);

        // Sync selection
        $this->user->languages()->sync($this->my_languages);

        // Upload file and save the avatar `url` on User model
        if ($this->photo) {
            // Hapus avatar lama jika ada
            if ($this->user->avatar) {
                $oldAvatarPath = public_path(str_replace('/storage', 'storage', $this->user->avatar));
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            // Simpan avatar baru
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        // You can toast and redirect to any route
        $this->success('User updated with success.', redirectTo: '/users');
    }
};

?>

<div>
    <x-header title="Update {{ $user->name }}" separator />

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
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>

    </x-form>
</div>
