<?php

use App\Models\Menu;
use App\Models\Kategori;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    // Create a public property.
    public int $kategori_id = 0;

    public int $filter = 0;

    public $page = [['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50'], ['id' => 100, 'name' => '100']];

    public int $perPage = 10; // Default jumlah data per halaman

    public int $stokFilter = 0;

    public bool $editModal = false; // Untuk menampilkan modal
    public ?Menu $editingMenu = null; // Menyimpan data Menu yang sedang diedit
    public string $editingName = '';
    public int $editingStok; // Menyimpan nilai input untuk nama Menu

    public function edit($id): void
    {
        $this->editingMenu = Menu::find($id);

        if ($this->editingMenu) {
            $this->editingName = $this->editingMenu->name;
            $this->editingStok = $this->editingMenu->stok;
            $this->editModal = true; // Tampilkan modal
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingStok' => 'required|integer|min:0',
        ]);

        if ($this->editingMenu) {
            // Update Menu
            $this->editingMenu->update([
                'name' => $this->editingName,
                'stok' => $this->editingStok,
            ]);

            $this->editModal = false;
            $this->success('Stok berhasil diubah.', position: 'toast-top');
            logActivity('updated', 'Merubah stok menu ' . $this->editingName);
        }
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-top');
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'avatar', 'label' => '', 'class' => 'w-1'], ['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'kategori_name', 'label' => 'Kategori'], ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'], ['key' => 'price', 'label' => 'Harga'], ['key' => 'stok', 'label' => 'Stok']];
    }

    public function menus(): LengthAwarePaginator
    {
        return Menu::query()
            ->withAggregate('kategori', 'name')
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->when($this->kategori_id, fn(Builder $q) => $q->where('kategori_id', $this->kategori_id))
            ->when($this->stokFilter === 1, fn(Builder $q) => $q->where('stok', '<', 10))
            ->when($this->stokFilter === 2, fn(Builder $q) => $q->where('stok', '>=', 10))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        if ($this->filter >= 0 && $this->filter < 3) {
            if (!$this->search == null) {
                $this->filter = 1;
            } else {
                $this->filter = 0;
            }
            if (!$this->kategori_id == 0) {
                $this->filter += 1;
            }
            if (!$this->stokFilter == 0) {
                $this->filter += 1;
            }
        }
        return [
            'menus' => $this->menus(),
            'headers' => $this->headers(),
            'kategori' => Kategori::all(),
            'perPage' => $this->perPage,
            'pages' => $this->page,
            'stokFilters' => [['id' => 0, 'name' => 'Semua'], ['id' => 1, 'name' => 'Stok dibawah minimum'], ['id' => 2, 'name' => 'Stok diatas minimum']],
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
    <x-header title="Menus" separator progress-indicator>
    </x-header>

    <!-- FILTERS -->
    <div class="grid grid-cols-1 md:grid-cols-8 gap-4  items-end mb-4">
        <div class="md:col-span-1">
            <x-select label="Show entries" :options="$pages" wire:model.live="perPage" class="w-15" />
        </div>
        <div class="md:col-span-6">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"
                class="" />
        </div>
        <div class="md:col-span-1 flex">
            <x-button spinner label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$menus" :sort-by="$sortBy" with-pagination
            @row-click="$wire.edit($event.detail.id)">
            @scope('cell_avatar', $menu)
                <x-avatar image="{{ $menu->photo ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button spinner class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            <x-select placeholder="Kategori" wire:model.live="kategori_id" :options="$kategori" icon="o-flag"
                placeholder-value="0" />
            <x-select label="" wire:model.live="stokFilter" :options="$stokFilters" icon="o-archive-box" />

        </div>

        <x-slot:actions>
            <x-button spinner label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button spinner label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>

    <x-modal wire:model="editModal" title="Edit Stok Menu">
        <div class="grid gap-4">
            <x-input label="Menu Name" wire:model="editingName" readonly/>
            <x-input label="Stok" wire:model="editingStok" type="number" min="0"/>
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.editModal=false" />
            <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveEdit" />
        </x-slot:actions>
    </x-modal>
</div>
