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
        $menu = Menu::findOrFail($id);
        if ($menu->photo && file_exists(public_path($menu->photo))) {
            unlink(public_path($menu->photo));
        }
        $menu->delete();
        $this->warning("Menu $menu->name akan dihapus", position: 'toast-top');
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
        <x-slot:actions>
            <x-button label="Create" link="/menus/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
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
            <x-button label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$menus" :sort-by="$sortBy" with-pagination
            link="menus/{id}/edit?name={name}&kategori={kategori.name}">
            @scope('cell_avatar', $menu)
                <x-avatar image="{{ $menu->photo ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('actions', $menu)
                <x-button icon="o-trash" wire:click="delete({{ $menu['id'] }})"
                    wire:confirm="Yakin ingin menghapus {{ $menu['name'] }}?" spinner
                    class="btn-ghost btn-sm text-red-500" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            <x-select placeholder="Kategori" wire:model.live="kategori_id" :options="$kategori" icon="o-flag"
                placeholder-value="0" />
            <x-select label="" wire:model.live="stokFilter" :options="$stokFilters" icon="o-archive-box" />

        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>
</div>
