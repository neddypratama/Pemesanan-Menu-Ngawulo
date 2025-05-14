<?php

use Livewire\Volt\Component;
use App\Models\Menu;
use App\Models\Kategori;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

new class extends Component {
    // We will use it later
    use Toast, WithFileUploads;

    // Component parameter
    public Menu $menu;

    #[Rule('required|unique:menus')]
    public string $name = '';

    #[Rule('required|integer|min:1')]
    public int $price;

    #[Rule('required|sometimes')]
    public ?int $kategori_id = null;
    
    #[Rule('required|integer|min:1')]
    public int $stok;

    #[Rule('nullable|image|max:1024')]
    public $photo;

    public string $avatar = '';

    #[Rule('sometimes')]
    public ?string $deskripsi = null;

    public function with(): array
    {
        return [
            'kategori' => Kategori::all(),
        ];
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Upload file and save the avatar `url` on User model
        if ($this->photo) {
            $url = $this->photo->store('menus', 'public');
            $data['photo'] = "/storage/$url";
        }

        // Create
        $menu = Menu::create($data);
        
        logActivity('created', $menu->name . ' ditambahkan');

        // You can toast and redirect to any route
        $this->success('Menu berhasil dibuat!', redirectTo: '/menus');
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
                <x-file label="Photo" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $menu->photo ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
                </x-file>
                <x-select label="Kategori" wire:model="kategori_id" :options="$kategori" placeholder="---" />
                <x-input label="Name" wire:model="name" />
                <x-input label="Harga" wire:model="price" prefix="Rp" money="IDR" />
                <x-input label="Stok" wire:model="stok" type="number" min="1"/>
            </div>
        </div>

        {{--  Details section --}}
        <hr class="my-5" />

        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Details" subtitle="More about the menu" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-editor wire:model="deskripsi" label="Deskripsi" hint="The great biography" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="/menus" />
            {{-- The important thing here is `type="submit"` --}}
            {{-- The spinner property is nice! --}}
            <x-button label="Create" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>

    </x-form>
</div>
