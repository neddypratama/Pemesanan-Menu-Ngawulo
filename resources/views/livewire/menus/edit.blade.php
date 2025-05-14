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

    #[Rule('required|max:225')]
    public string $name = '';

    #[Rule('required|integer|min:1')]
    public int $price;

    #[Rule('required|integer|min:1')]
    public int $stok;

    #[Rule('required|sometimes')]
    public ?int $kategori_id = null;

    #[Rule('nullable|image|max:1024')]
    public $foto;

    #[Rule('sometimes')]
    public ?string $deskripsi = null;

    public function with(): array
    {
        return [
            'kategori' => Kategori::all(),
        ];
    }

    public function mount(): void
    {
        $this->fill($this->menu);
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Update
        $this->menu->update($data);

        // Upload file and save the avatar `url` on User model
        if ($this->foto) {
            // Hapus avatar lama jika ada
            if ($this->menu->photo) {
                $oldAvatarPath = public_path(str_replace('/storage', 'storage', $this->menu->photo));
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            // Simpan avatar baru
            $url = $this->foto->store('menus', 'public');
            $this->menu->update(['photo' => "/storage/$url"]);
        }

        logActivity('updated', 'Merubah data menu ' . $this->menu->name);

        // You can toast and redirect to any route
        $this->success('Menu updated with success.', redirectTo: '/menus');
    }
};

?>

<div>
    <x-header title="Update {{ $menu->name }}" separator />

    <x-form wire:submit="save">
        {{--  Basic section  --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic" subtitle="Basic info from menu" size="text-2xl" />
            </div>

            <div class="col-span-3 grid gap-3">
                <x-file label="Photo" wire:model="foto" accept="image/png, image/jpeg" crop-after-change>
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
            <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>

    </x-form>
</div>
