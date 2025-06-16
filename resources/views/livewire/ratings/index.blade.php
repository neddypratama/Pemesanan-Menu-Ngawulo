<?php

use App\Models\Rating;
use App\Models\Menu;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RatingExport;

new class extends Component {
    use Toast, WithPagination, WithFileUploads;
    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public int $filter = 0;

    public $page = [['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50'], ['id' => 100, 'name' => '100']];

    public int $perPage = 10; // Default jumlah data per halaman

    public bool $editModal = false; // Untuk menampilkan modal

    public ?Rating $editingRating = null; // Menyimpan data Resep yang sedang diedit

    public string $editingReview = ''; // Untuk menyimpan input nama Resep baru
    public int $editingMenu;
    public int $editingRate = 0;

    public bool $createModal = false; // Untuk menampilkan modal create

    public string $newReview = ''; // Untuk menyimpan input nama Resep baru
    public int $newMenu;
    public int $newRating = 0;

    public bool $detailModal = false; // Untuk menampilkan modal create

    public ?Rating $detailRating = null; // Menyimpan data Rating yang sedang diedit
    public string $detailReview = ''; // Untuk menyimpan input nama Resep baru
    public string $detailMenu;
    public int $detailRate = 0;

    public int $menu_id = 0;

    public $menu;

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
        $rating = Rating::findOrFail($id);
        logActivity('deleted', 'Menghapus rating' . $rating->id);
        $rating->delete();
        $this->warning("Rating $rating->id akan dihapus", position: 'toast-top');
    }

    public function create(): void
    {
        $this->newReview = ''; // Reset input sebelum membuka modal
        $this->newRating = 0;
        // $this->newResepMenu;
        $this->createModal = true;
    }

    public function saveCreate(): void
    {
        $this->validate([
            'newMenu' => 'required|sometimes',
            'newReview' => 'sometimes',
            'newRating' => 'required',
        ]);

        $rating = Rating::create(['menu_id' => $this->newMenu, 'rating' => $this->newRating, 'review' => $this->newReview]);
        logActivity('created', $rating->id . ' ditambahkan');

        $this->createModal = false;
        $this->success('Rating created successfully.', position: 'toast-top');
    }

    public function detail($id): void
    {
        $this->detailRating = Rating::find($id);

        if ($this->detailRating) {
            $this->detailRate = $this->detailRating->rating;
            $this->detailReview = $this->detailRating->review;
            $this->detailMenu = $this->detailRating->menu->name;
            $this->detailModal = true; // Tampilkan modal
        }
    }

    public function edit($id): void
    {
        $this->editingRating = Rating::find($id);

        if ($this->editingRating) {
            $this->editingRate = $this->editingRating->rating;
            $this->editingReview = $this->editingRating->review;
            $this->editingMenu = $this->editingRating->menu_id;
            $this->editModal = true; // Tampilkan modal
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingMenu' => 'required|sometimes',
            'editingReview' => 'sometimes',
            'editingRate' => 'required',
        ]);

        if ($this->editingRating) {
            // Update Resep
            $this->editingRating->update([
                'rating' => $this->editingRate,
                'review' => $this->editingReview,
                'menu_id' => $this->editingMenu,
            ]);

            logActivity('updated', 'Merubah data rating ' . $this->editingRating->id);

            $this->editModal = false;
            $this->success('Rating updated successfully.', position: 'toast-top');
        }
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'avatar', 'label' => '', 'class' => 'w-1'], ['key' => 'id', 'label' => '#'], ['key' => 'menu_name', 'label' => 'Menu'], ['key' => 'rating', 'label' => 'Rating'], ['key' => 'review', 'label' => 'Review', 'class' => 'w-100', 'sortable' => false], ['key' => 'created_at', 'label' => 'Tanggal dibuat', 'class' => 'w-30']];
    }

    public function rating(): LengthAwarePaginator
    {
        return Rating::query()
            ->select(['id', 'menu_id', 'rating', 'review', 'created_at']) // Pastikan rating ikut dipilih
            ->withAggregate('menu', 'name')
            ->when($this->menu_id, fn(Builder $q) => $q->where('menu_id', $this->menu_id))
            ->when($this->search, fn(Builder $q) => $q->whereHas('menu', fn(Builder $query) => $query->where('name', 'like', "%$this->search%")))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
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
            'rating' => $this->rating(),
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

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        logActivity('export', 'Mencetak data rating');
        return Excel::download(new RatingExport(), 'ratings.xlsx');
    }
};

?>

<div>
    <!-- HEADER -->
    <x-header title="Ratings" separator progress-indicator>
        <x-slot:actions>
            <x-button spinner label="Create" @click="$wire.create()" responsive icon="o-plus" class="btn-primary" />
            <x-button spinner label="Export" wire:click="export" icon="o-arrow-down-tray" class="btn-secondary" responsive />
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
            <x-button spinner label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}" responsive />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$rating" :sort-by="$sortBy" with-pagination @row-click="$wire.edit($event.detail.id)">
            @scope('cell_rating', $rating)
                <div class="flex text-yellow-500">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $rating->rating)
                            <x-icon name="fas.star" class="w-5 h-5" />
                        @else
                            <x-icon name="fas.star" class="w-5 h-5 text-gray-300" />
                        @endif
                    @endfor
                </div>
            @endscope

            @scope('actions', $rating)
                <div class="flex">
                    <x-button spinner tooltip="Review" icon="fas.circle-info" class="btn-ghost btn-sm text-slate-500"
                        wire:click.stop="detail({{ $rating['id'] }})" />
                    <x-button spinner icon="o-trash" wire:click.stop="delete({{ $rating['id'] }})"
                        wire:confirm="Yakin ingin menghapus {{ $rating['name'] }}?" spinner
                        class="btn-ghost btn-sm text-red-500" />
                @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Create Rating">
        <div class="grid gap-4">
            <x-select label="Menu" wire:model.live="newMenu" :options="$menus" placeholder="---" />
            <x-rating label="Rating" wire:model.live="newRating" class="bg-warning" total="5" />
            <x-textarea wire:model.live="newReview" label="Review" hint="The great review" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.createModal=false" />
            <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveCreate" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="editModal" title="Edit Rating">
        <div class="grid gap-4">
            <x-select label="Menu" wire:model.live="editingMenu" :options="$menus" placeholder="---" />
            <x-rating label="Rating" wire:model.live="editingRate" class="bg-warning" total="5" />
            <x-textarea wire:model.live="editingReview" label="Review" hint="The great review" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.editModal=false" />
            <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveEdit" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="detailModal" title="Detail Review">
        <div class="grid gap-4">
            <x-input label="Menu" wire:model.live="detailMenu" readonly />
            <x-rating label="Rating" wire:model.live="detailRate" class="bg-warning" total="5" disabled />
            <x-textarea wire:model.live="detailReview" label="Review" hint="The great review" readonly />
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" icon="o-x-mark" @click="$wire.detailModal=false" />
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
