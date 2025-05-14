<?php

use App\Models\Kategori;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;

new class extends Component {
    use Toast, WithPagination, WithFileUploads;
    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    // Create a public property.
    // public int $country_id = 0;

    public int $filter = 0;

    public $page = [['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50'], ['id' => 100, 'name' => '100']];

    public int $perPage = 10; // Default jumlah data per halaman

    public bool $editModal = false; // Untuk menampilkan modal

    public ?Kategori $editingKategori = null; // Menyimpan data Kategori yang sedang diedit

    public string $editingName = ''; // Menyimpan nilai input untuk nama Kategori
    public string $editingImage = '';

    public bool $createModal = false; // Untuk menampilkan modal create

    public string $newKategoriName = ''; // Untuk menyimpan input nama Kategori baru
    public string $newKategoriImage = '';

    public $newphoto;
    public $editphoto;

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-top');
    }

    // Delete action
    public function delete($id): void
    {
        $kategori = Kategori::findOrFail($id);
        if ($kategori->image && file_exists(public_path($kategori->image))) {
            unlink(public_path($kategori->image));
        }
        logActivity('deleted', 'Menghapus data kategori ' . $kategori->name);
        $kategori->delete();
        $this->warning("Kategori $kategori->name akan dihapus", position: 'toast-top');
    }

    public function create(): void
    {
        $this->newKategoriName = ''; // Reset input sebelum membuka modal
        $this->newKategoriImage = '';
        $this->newphoto = null;
        $this->createModal = true;
    }

    public function saveCreate(): void
    {
        $this->validate([
            'newKategoriName' => 'required|string|max:255',
            'newphoto' => 'nullable|image|max:1024',
        ]);

        // Upload file and save the avatar `url` on User model
        if ($this->newphoto) {
            $url = $this->newphoto->store('categories', 'public');
            $this->newKategoriImage = "/storage/$url";
        }

        Kategori::create(['name' => $this->newKategoriName, 'image' => $this->newKategoriImage]);

        logActivity('created', $this->newKategoriName . ' ditambahkan');

        $this->createModal = false;
        $this->success('Kategori created successfully.', position: 'toast-top');
    }

    public function edit($id): void
    {
        $this->editingKategori = Kategori::find($id);

        if ($this->editingKategori) {
            $this->editingName = $this->editingKategori->name;
            $this->editphoto = $this->editingKategori->image;
            $this->editModal = true; // Tampilkan modal
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editphoto' => 'nullable|image|max:1024',
        ]);

        if ($this->editingKategori) {
            // Hapus gambar lama jika ada file baru diunggah
            if ($this->editphoto instanceof \Illuminate\Http\UploadedFile) {
                if ($this->editingKategori->image) {
                    $oldImagePath = public_path(str_replace('/storage', 'storage', $this->editingKategori->image));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Simpan gambar baru
                $url = $this->editphoto->store('categories', 'public');
                $this->editingKategori->image = "/storage/$url";
            }

            // Update kategori
            $this->editingKategori->update([
                'name' => $this->editingName,
                'image' => $this->editingKategori->image,
            ]);

            logActivity('updated', 'Merubah data kategori ' . $this->editingName);

            $this->editModal = false;
            $this->success('Kategori updated successfully.', position: 'toast-top');
        }
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'avatar', 'label' => '', 'class' => 'w-5'],
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-100'],
            ['key' => 'menus_count', 'label' => 'Menu', 'class' => 'w-100'], // Gunakan `users_count`
            ['key' => 'created_at', 'label' => 'Tanggal dibuat', 'class' => 'w-30'],
        ];
    }

    public function kategori(): LengthAwarePaginator
    {
        return Kategori::query()
            ->withCount('menus') // Menghitung jumlah users di setiap role
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        if ($this->filter >= 0 && $this->filter < 2) {
            if (!$this->search == null) {
                $this->filter = 1;
            } else {
                $this->filter = 0;
            }
        }
        return [
            'kategori' => $this->kategori(),
            'headers' => $this->headers(),
            'perPage' => $this->perPage,
            'pages' => $this->page,
        ];
    }

    // Reset pagination when any component property changes
    public function updated($property): void
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }
};

?>

<div>
    <!-- HEADER -->
    <x-header title="Categories" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Create" @click="$wire.create()" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- FILTERS -->
    <div class="grid grid-cols-1 md:grid-cols-8 gap-4 items-end mb-4">
        <div class="md:col-span-1">
            <x-select label="Show entries" :options="$pages" wire:model.live="perPage" />
        </div>
        <div class="md:col-span-6">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"
                class="" />
        </div>
        <div class="md:col-span-1 flex">
            <x-button label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" responsive/>
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$kategori" :sort-by="$sortBy" with-pagination
            @row-click="$wire.edit($event.detail.id)">
            @scope('cell_avatar', $kategori)
                <x-avatar image="{{ $kategori->image ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('cell_menus_count', $kategori)
                <span>{{ $kategori->menus_count }}</span>
            @endscope
            @scope('actions', $kategori)
                <x-button icon="o-trash" wire:click="delete({{ $kategori['id'] }})"
                    wire:confirm="Yakin ingin menghapus {{ $kategori['name'] }}?" spinner
                    class="btn-ghost btn-sm text-red-500" />
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Create Kategori">
        <div class="grid gap-4">
            <x-file label="Image" wire:model.live="newphoto" accept="image/png, image/jpeg" crop-after-change>
                <img src="{{ $kategori->image ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
            </x-file>
            <x-input label="Kategori Name" wire:model.live="newKategoriName" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" @click="$wire.createModal=false" />
            <x-button label="Save" icon="o-check" class="btn-primary" wire:click="saveCreate" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="editModal" title="Edit Kategori">
        <div class="grid gap-4">
            <x-file label="Image" wire:model.live="editphoto" accept="image/png, image/jpeg" crop-after-change>
                <img src="{{ $kategori->image ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
            </x-file>
            <x-input label="Kategori Name" wire:model.live="editingName" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" @click="$wire.editModal=false" />
            <x-button label="Save" icon="o-check" class="btn-primary" wire:click="saveEdit" />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            {{-- <x-select placeholder="Country" wire:model.live="country_id" :options="$countries" icon="o-flag"
                placeholder-value="0" /> --}}
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>
</div>
