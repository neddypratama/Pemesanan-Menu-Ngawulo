<?php

use App\Models\Transaksi;
use App\Models\Order;
use App\Models\User;
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
    public int $user_id = 0;

    public int $filter = 0;

    public $page = [['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50'], ['id' => 100, 'name' => '100']];

    public int $perPage = 10; // Default jumlah data per halaman

    public bool $showDetail = false;
    public ?Transaksi $detailTransaksi = null;

    public function detail($id)
    {
        $this->detailTransaksi = Transaksi::with('orders.menu', 'user')->find($id); // pastikan relasi 'order' ada

        if (!$this->detailTransaksi) {
            $this->error('Transaksi tidak ditemukan.');
            return;
        }

        $this->showDetail = true;
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
        return [['key' => 'invoice', 'label' => 'INV', 'class' => 'w-64'], ['key' => 'user_name', 'label' => 'User', 'class' => 'w-48'], ['key' => 'total', 'label' => 'Total', 'format' => ['currency', '2,.', 'Rp. '], 'class' => 'w-36'], ['key' => 'status', 'label' => 'Status', 'class' => 'w-24'], ['key' => 'tanggal', 'label' => 'Tanggal', 'class' => 'w-40', 'format' => ['date', 'H:i:s d/m/Y']]];
    }

    public function transaksis(): LengthAwarePaginator
    {
        return Transaksi::query()
            ->withAggregate('user', 'name')
            ->where('status', 'success')
            // ->where('tanggal', now())
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
            ->orderBy('id', 'asc')
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

    public function selesaikan($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            $this->error('Transaksi tidak ditemukan atau sudah diselesaikan.');
            return;
        }

        $transaksi->update(['status' => 'deliver']);
        $this->success('Pesanan siap diantar!', position: 'toast-top');
        logActivity('deliver', 'Merubah status dekiver transaksi ' . $transaksi->invoice);
    }
};

?>

<div>
    <!-- HEADER -->
    <x-header title="Transactions" separator progress-indicator>
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
        <x-table :headers="$headers" :rows="$transaksi" :sort-by="$sortBy" with-pagination
            link="orders/{id}/detail?invoice={invoice}&user={user.name}" show-empty-text empty-text="Data tidak ada!">

            @scope('cell_status', $transaksi)
                @php
                    $colors = [
                        'success' => 'border-green-500 text-green-500',
                        'pending' => 'border-yellow-500 text-yellow-500',
                        'cancel' => 'border-red-500 text-red-500',
                    ];
                @endphp

                <span class="px-2 py-1 border rounded-md {{ $colors[$transaksi['status']] ?? 'border-gray-300 ' }}">
                    {{ ucfirst($transaksi['status']) }}
                </span>
            @endscope

            @scope('actions', $transaksi)
                <div class="flex space-x-2">
                    <x-button icon="o-eye" tooltip="Detail" wire:click="detail({{ $transaksi['id'] }})"
                        class="btn-warning btn-sm" />

                    <x-button icon="o-check-circle" tooltip="Selesaikan" class="btn-success btn-sm"
                        @click="if (confirm('Yakin ingin menyelesaikan pesanan ini?')) { $wire.selesaikan({{ $transaksi['id'] }}) }" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="showDetail" title="Detail Order" max-width="2xl">
        @if ($detailTransaksi)
            <div class="space-y-6">
                <!-- Info utama -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="font-semibold ">Invoice</p>
                        <p class="">{{ $detailTransaksi['invoice'] }}</p>
                    </div>
                    <div>
                        <p class="font-semibold ">User</p>
                        <p class="">{{ $detailTransaksi['user']['name'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold ">Total</p>
                        <p class=" ">Rp {{ number_format($detailTransaksi['total'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="font-semibold ">Tanggal</p>
                        <p class="">
                            {{ \Carbon\Carbon::parse($detailTransaksi['tanggal'])->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div>
                    <!-- Daftar Produk -->
                    @if (!empty($detailTransaksi['orders']))
                        <div>
                            <h3 class="font-semibold  mb-2">Daftar Produk</h3>
                            <div class="space-y-2">
                                @foreach ($detailTransaksi['orders'] as $item)
                                    <div class=" p-3 rounded-lg shadow-sm flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium ">{{ $item['menu']['name'] }}
                                            </p>
                                            <p class="text-xs ">Qty: {{ $item['qty'] }}</p>
                                        </div>
                                        @if ($item['keterangan'] == null)
                                            <div class="text-sm font-semibold ">
                                                Tidak ada catatan
                                            </div>
                                        @else
                                            <div class="text-sm font-semibold ">
                                                {{ $item['keterangan'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <x-slot:footer>
            <x-button label="Tutup" class="btn-secondary" @click="$wire.showDetail = false" />
        </x-slot:footer>
    </x-modal>



    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            <x-select placeholder="User" wire:model.live="user_id" :options="$users" icon="o-flag"
                placeholder-value="0" />
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer=false" />
        </x-slot:actions>
    </x-drawer>
</div>
