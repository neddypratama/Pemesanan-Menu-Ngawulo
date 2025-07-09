<?php

use App\Models\Transaksi;
use App\Models\Order;
use App\Models\User;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransaksiExport;

new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    // Create a public property.
    public int $user_id = 0;

    public int $filter = 0;

    public $page = [['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50'], ['id' => 100, 'name' => '100']];

    public int $perPage = 10; // Default jumlah data per halaman

    public bool $showExportModal = false;
    public string $exportMode = 'all'; // 'all', 'range', 'month'
    public ?string $startDate = null;
    public ?string $endDate = null;

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
        $transaksi = Transaksi::findOrFail($id);
        $orders = Order::where('transaksi_id', $id)->get();

        logActivity('deleted', 'Menghapus data transaksi ' . $transaksi->invoice);

        // Log semua ID order yang dihapus (jika ingin dicatat)
        foreach ($orders as $order) {
            logActivity('deleted', 'Menghapus data order ID: ' . $order->id);
        }

        // Hapus semua orders
        Order::where('transaksi_id', $id)->delete();

        // Hapus transaksi
        $transaksi->delete();

        $this->warning("Transaksi $transaksi->invoice telah dihapus", position: 'toast-top');
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'invoice', 'label' => 'INV', 'class' => 'w-40'], ['key' => 'user_name', 'label' => 'User', 'class' => 'w-50'], ['key' => 'total', 'label' => 'Total', 'format' => ['currency', '2,.', 'Rp. ']], ['key' => 'status', 'label' => 'Status'], ['key' => 'tanggal', 'label' => 'Tanggal', 'class' => 'w-20', 'format' => ['date', 'H:i:s d/m/Y']]];
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = Transaksi::with('user', 'orders.menu');

        if ($this->exportMode === 'range' && $this->startDate && $this->endDate) {
            $query->whereBetween('tanggal', [$this->startDate, $this->endDate]);
        } elseif ($this->exportMode === 'month') {
            $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);
        }

        $data = $query->get();
        $this->showExportModal = false;
        logActivity('export', 'Mencetak data transaksi');

        return Excel::download(new TransaksiExport($data), 'transaksi.xlsx');
    }

    public function transaksis(): LengthAwarePaginator
    {
        return Transaksi::query()
            ->withAggregate('user', 'name')
            ->when($this->search, function (Builder $q) {
                if (is_numeric($this->search)) {
                    // dd($this->search);
                    // Jika search adalah angka, cari di total
                    $q->where('total', 'like', $this->search . '%');
                } else {
                    // Jika bukan angka, cari di invoice
                    $q->where('invoice', 'like', "%{$this->search}%");
                }
            })
            ->when($this->user_id, fn(Builder $q) => $q->where('user_id', $this->user_id))
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
            if (!$this->user_id == 0) {
                $this->filter += 1;
            }
        }
        return [
            'transaksi' => $this->transaksis(),
            'headers' => $this->headers(),
            'users' => User::all(),
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
    <x-header title="Transactions" separator progress-indicator>
        <x-slot:actions>
            <x-button spinner label="Create" link="/orders/create" responsive icon="o-plus" class="btn-primary" />
            <x-button spinner label="Export" wire:click="$set('showExportModal', true)" icon="o-arrow-down-tray"
                class="btn-secondary" responsive />
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
            <x-button spinner label="Filters" @click="$wire.drawer=true" icon="o-funnel" badge="{{ $filter }}"
                class="" responsive />
        </div>
        <!-- Dropdown untuk jumlah data per halaman -->
    </div>

    <!-- TABLE wire:poll.5s="users"  -->
    <x-card>
        <x-table :headers="$headers" :rows="$transaksi" :sort-by="$sortBy" with-pagination
            link="orders/{id}/detail?invoice={invoice}&user={user.name}">
            @scope('cell_user_name', $transaksi)
                {{ $transaksi['user_name'] ?? ($transaksi['guest_name'] ?? '-') }}
            @endscope
            @scope('cell_status', $transaksi)
                @php
                    $colors = [
                        'success' => 'border-green-500 text-green-500',
                        'pending' => 'border-yellow-500 text-yellow-500',
                        'cancel' => 'border-red-500 text-red-500',
                    ];
                @endphp

                <span
                    class="px-2 py-1 border rounded-md {{ $colors[$transaksi['status']] ?? 'border-gray-300 text-gray-500' }}">
                    {{ ucfirst($transaksi['status']) }}
                </span>
            @endscope
            @scope('actions', $transaksi)
                <div class="flex">
                    <x-button spinner tooltip="Edit" icon="fas.pencil" class="btn-ghost btn-sm text-warning"
                        link="{{ route('orders.edit', ['id' => $transaksi['id']]) }}" />
                    <x-button spinner tooltip="Delete" icon="o-trash" wire:click="delete({{ $transaksi['id'] }})"
                        wire:confirm="Yakin ingin menghapus {{ $transaksi['invoice'] }}?" spinner
                        class="btn-ghost btn-sm text-red-500" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button spinner class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            <x-select placeholder="User" wire:model.live="user_id" :options="$users" icon="o-flag"
                placeholder-value="0" />
        </div>

        <x-slot:actions>
            <x-button spinner label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button spinner label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>

    <x-modal wire:model="showExportModal" title="Export Transaksi">
        <div class="grid gap-4">
            <x-select label="Mode Export" wire:model.live="exportMode" :options="[
                ['id' => 'all', 'name' => 'Semua Data'],
                ['id' => 'range', 'name' => 'Berdasarkan Tanggal'],
                ['id' => 'month', 'name' => 'Bulan Ini'],
            ]" />

            @if ($exportMode === 'range')
                <x-input type="date" label="Dari Tanggal" wire:model.live="startDate" />
                <x-input type="date" label="Sampai Tanggal" wire:model.live="endDate" />
            @endif
        </div>

        <x-slot:actions>
            <x-button spinner label="Batal" @click="$set('showExportModal', false)" />
            <x-button spinner label="Export" wire:click="export" spinner class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
