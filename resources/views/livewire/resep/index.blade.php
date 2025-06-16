<?php

use App\Models\Resep;
use App\Models\Menu;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public bool $detailModal = false; // Untuk menampilkan modal create

    public ?Resep $detailResep = null; // Menyimpan data Resep yang sedang diedit
    public string $detailResepName = ''; // Untuk menyimpan input nama Resep baru
    public string $detailResepMenu;

    public int $menu_id = 0;

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-top');
    }

    public function detail($id): void
    {
        $this->detailResep = Resep::find($id);

        if ($this->detailResep) {
            $this->detailResepName = $this->detailResep->resep;
            $this->detailResepMenu = $this->detailResep->menu->name;
            $this->detailModal = true; // Menampilkan modal
        }
    }

    public function closeDetail(): void
    {
        logger('closeDetail dipanggil'); // Debugging log
        $this->detailModal = false;
        $this->reset('detailResep', 'detailResepName', 'detailResepMenu');
    }

    public function create(): void
    {
        $this->newResepName = ''; // Reset input sebelum membuka modal
        // $this->newResepMenu;
        $this->createModal = true;
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'menu_name', 'label' => 'Menu'], ['key' => 'created_at', 'label' => 'Tanggal dibuat', 'class' => 'w-30']];
    }

    public function resep(): LengthAwarePaginator
    {
        $data = Resep::query()->withAggregate('menu', 'name')->when($this->menu_id, fn(Builder $q) => $q->where('menu_id', $this->menu_id))->orderBy(...array_values($this->sortBy))->paginate($this->perPage);

        // dd($data);
        return $data;
    }

    public function with(): array
    {
        if ($this->filter >= 0 && $this->filter < 1) {
            if (!$this->search == null) {
                $this->filter = 1;
            } else {
                $this->filter = 0;
            }
            if (!$this->menu_id == 0) {
                $this->filter += 1;
            }
        }
        return [
            'resep' => $this->resep(),
            'headers' => $this->headers(),
            'menus' => Menu::all(),
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
    <x-header title="Recipes" separator progress-indicator>
    </x-header>

    <!-- FILTERS -->
    <div class="grid grid-cols-1 md:grid-cols-8 gap-4 items-end mb-4">
        <div class="md:col-span-1">
            <x-select label="Show entries" :options="$pages" wire:model.live="perPage" />
        </div>
        <div class="md:col-span-6">
            {{-- <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"
                class="" /> --}}
        </div>
        <div class="md:col-span-1 flex">
            <x-button label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" responsive />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$resep" :sort-by="$sortBy" with-pagination @row-click="$wire.detail($event.detail.id)">
        </x-table>
    </x-card>

    <x-modal wire:model.live="detailModal" title="Detail Resep">
        <div class="grid gap-4">
            <x-input label="Menu" wire:model.live="detailResepMenu" readonly />
            <x-editor wire:model.live="detailResepName" label="Resep" hint="The great resep" readonly />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" icon="o-x-mark" wire:click="closeDetail" />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            {{-- <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" /> --}}
            <x-select placeholder="Menu" wire:model.live="menu_id" :options="$menus" icon="o-flag"
                placeholder-value="0" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button spinner label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>
</div>
