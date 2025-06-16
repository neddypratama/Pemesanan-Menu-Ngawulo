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

    public bool $editModal = false; // Untuk menampilkan modal

    public ?Resep $editingResep = null; // Menyimpan data Resep yang sedang diedit

    public string $editingName = ''; // Menyimpan nilai input untuk nama Resep
    public int $editingMenu;

    public bool $createModal = false; // Untuk menampilkan modal create

    public string $newResepName = ''; // Untuk menyimpan input nama Resep baru
    public int $newResepMenu;

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

    // Delete action
    public function delete($id): void
    {
        $resep = Resep::findOrFail($id);
        logActivity('deleted', 'Menghapus role ' . $resep->id);
        $resep->delete();
        $this->warning("Resep $resep->name akan dihapus", position: 'toast-top');
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

    public function saveCreate(): void
    {
        $this->validate([
            'newResepName' => 'required|sometimes',
            'newResepMenu' => 'sometimes',
        ]);

        $resep = Resep::create(['resep' => $this->newResepName, 'menu_id' => $this->newResepMenu]);
        logActivity('created', $resep->id . ' ditambahkan');

        $this->createModal = false;
        $this->success('Resep created successfully.', position: 'toast-top');
    }

    public function edit($id): void
    {
        $this->editingResep = Resep::find($id);

        if ($this->editingResep) {
            $this->editingName = $this->editingResep->resep;
            $this->editingMenu = $this->editingResep->menu_id;
            $this->editModal = true; // Tampilkan modal
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingName' => 'required|sometimes',
            'editingMenu' => 'sometimes',
        ]);

        if ($this->editingResep) {
            // Update Resep
            $this->editingResep->update([
                'resep' => $this->editingName,
                'menu_id' => $this->editingMenu,
            ]);

            logActivity('updated', 'Merubah data resep ' . $this->editingResep->id);
            $this->editModal = false;
            $this->success('Resep updated successfully.', position: 'toast-top');
        }
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'menu_name', 'label' => 'Menu'], ['key' => 'created_at', 'label' => 'Tanggal dibuat', 'class' => 'w-30']];
    }

    public function resep(): LengthAwarePaginator
    {
        $data = Resep::query()
            ->withAggregate('menu', 'name')
            ->when($this->search, function (Builder $q) {
                $q->whereHas('menu', function (Builder $query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->menu_id, function (Builder $q) {
                $q->where('menu_id', $this->menu_id);
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        // dd($data);
        return $data;
    }

    public function with(): array
    {
        if ($this->filter >= 0 && $this->filter < 2) {
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
    {{-- <dd>{{ $this->detailModal }}</dd> --}}
    <x-header title="Recipes" separator progress-indicator>
        <x-slot:actions>
            <x-button spinner label="Create" @click="$wire.create()" responsive icon="o-plus" class="btn-primary" />
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
            <x-button spinner label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" responsive />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$resep" :sort-by="$sortBy" with-pagination  @row-click="$wire.edit($event.detail.id)">
            @scope('actions', $resep)
                <div class="flex">
                    <x-button spinner tooltip="Resep" icon="fas.circle-info" class="btn-ghost btn-sm text-slate-500"
                        wire:click.stop="detail({{ $resep['id'] }})" />
                    <x-button spinner icon="o-trash" wire:click.stop="delete({{ $resep['id'] }})"
                        wire:confirm="Yakin ingin menghapus {{ $resep['name'] }}?" spinner
                        class="btn-ghost btn-sm text-red-500" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Create Resep">
        <div class="grid gap-4">
            <x-select label="Menu" wire:model.live="newResepMenu" :options="$menus" placeholder="---" />
            <x-editor wire:model.live="newResepName" label="Resep" hint="The great resep" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.createModal=false" />
            <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveCreate" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="editModal" title="Edit Resep">
        <div class="grid gap-4">
            <x-select label="Menu" wire:model.live="editingMenu" :options="$menus" placeholder="---" />
            <x-editor wire:model.live="editingName" label="Resep" hint="The great resep" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.editModal=false" />
            <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveEdit" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model.ive="detailModal" title="Detail Resep">
        <div class="grid gap-4">
            <x-input label="Menu" wire:model.live="detailResepMenu" readonly />
            <x-editor wire:model.live="detailResepName" label="Resep" hint="The great resep" readonly />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" wire:click="closeDetail" />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button spinner class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            <x-select placeholder="Menu" wire:model.live="menu_id" :options="$menus" icon="o-flag"
                placeholder-value="0" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button spinner label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>
</div>
