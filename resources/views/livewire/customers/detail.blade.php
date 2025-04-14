<?php

use App\Models\Transaksi;
use App\Models\User;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

new class extends Component {
    use Toast;
    use WithPagination;

    public User $customer;
    public array $favorite = [];
    public string $search = '';

    public bool $drawer = false;
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-top');
    }

    public function headers(): array
    {
        return [['key' => 'invoice', 'label' => 'INV', 'class' => 'w-40'], ['key' => 'tanggal', 'label' => 'Tanggal', 'class' => 'w-64', 'format' => ['date', 'H:i:s d/m/Y']], ['key' => 'total', 'label' => 'Total', 'format' => ['currency', '2,.', 'Rp. ']], ['key' => 'status', 'label' => 'Status']];
    }

    public function transaksis(): LengthAwarePaginator
    {
        return Transaksi::query()
            ->where('user_id', $this->customer->id)
            ->with(['orders.menu.kategori'])
            ->when($this->search, function (Builder $q) {
                if (is_numeric($this->search)) {
                    $q->where('total', 'like', $this->search . '%');
                } else {
                    $q->where('invoice', 'like', "%{$this->search}%");
                }
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function favorite(): void
    {
        $this->favorite = Transaksi::where('user_id', $this->customer->id)
            ->with(['orders.menu.kategori'])
            ->get()
            ->flatMap->orders->groupBy('menu_id')
            ->map(
                fn($orders) => [
                    'menu' => optional($orders->first()->menu)->toArray(),
                    'total_qty' => $orders->sum('qty'),
                ],
            )
            ->sortByDesc('total_qty')
            ->take(2)
            ->values()
            ->toArray(); // Pastikan hasilnya berupa array biasa
    }

    public function with(): array
    {
        return [
            'transaksi' => $this->transaksis(),
            'favorite' => $this->favorite,
            'headers' => $this->headers(),
            'users' => User::all(),
            'perPage' => $this->perPage,
        ];
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }

    public function mount(User $customer): void
    {
        $this->customer = $customer;
        $this->favorite();
    }
};
?>
<div class="p-2">
    <x-header title="{{ $customer->name }}" separator progress-indicator></x-header>

    <div class="grid lg:grid-cols-2 gap-8">
        <!-- Info User -->
        <div class="card bg-base-100 rounded-lg p-5 shadow-sm">
            <div class="pb-5">
                <div class="text-2xl font-bold">Info</div>
                <hr class="mt-3">
            </div>
            <div class="flex items-center gap-2">
                <div class="avatar me-5">
                    <div class="w-20 rounded-full">
                        <img src="{{ $customer->avatar }}">
                    </div>
                </div>
                <div>
                    <div class="font-semibold">{{ $customer->name }}</div>
                    <div class="text-sm text-gray-400 flex flex-col gap-2">
                        <div class="inline-flex items-center gap-1">
                            <x-icon name="o-envelope" />
                            {{ $customer->email }}
                        </div>
                        <div class="inline-flex items-center gap-1">
                            <x-icon name="o-phone" />
                            {{ $customer->no_hp }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Favorit User -->
        <div class="card bg-base-100 rounded-lg p-5 shadow-sm">
            <div class="pb-5">
                <div class="text-2xl font-bold">Favorites</div>
                <hr class="mt-3">
            </div>
            <div>
                @foreach ($favorite as $fav)
                    @if ($fav['menu'])
                        <a href="/menus/{{ $fav['menu']['id'] }}/edit">
                            <div class="flex items-center gap-4 px-3 hover:bg-base-200/50 cursor-pointer mb-5">
                                <div class="avatar">
                                    <div class="w-11 rounded-full">
                                        <img src="{{ $fav['menu']['photo'] ?? 'default.jpg' }}">
                                    </div>
                                </div>
                                <div class="flex-1 overflow-hidden whitespace-nowrap">
                                    <div class="font-semibold">{{ $fav['menu']['name'] ?? 'Unknown' }}</div>
                                    <div class="text-gray-400 text-sm">
                                        {{ $fav['menu']['kategori']['name'] ?? 'Tanpa Kategori' }}
                                    </div>
                                </div>
                                <div class="badge font-bold">{{ $fav['total_qty'] }}</div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="grid grid-cols-1 md:grid-cols-8 gap-4 items-end mb-4 mt-5">
        <div class="md:col-span-1">
            <x-select label="Show entries" :options="[10, 25, 50, 100]" wire:model.live="perPage" />
        </div>
        <div class="md:col-span-7">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </div>
    </div>

    <!-- Table -->
    <x-card>
        <x-table :headers="$headers" :rows="$transaksi" with-pagination link="/orders/{id}/detail">
            @scope('cell_status', $rating)
                @php
                    $colors = [
                        'success' => 'border-green-500 text-green-500',
                        'pending' => 'border-yellow-500 text-yellow-500',
                        'cancel' => 'border-red-500 text-red-500',
                    ];
                @endphp
                <span
                    class="px-2 py-1 border rounded-md {{ $colors[$rating['status']] ?? 'border-gray-300 text-gray-500' }}">
                    {{ ucfirst($rating['status']) }}
                </span>
            @endscope
            @scope('cell_link', $transaksi)
                <a href="{{ url('orders/' . $transaksi['id'] . '/detail') }}">
                    {{ $transaksi['invoice'] }}
                </a>
            @endscope
        </x-table>
    </x-card>

    <div class="mt-6">
        <x-button label="Kembali" link="/customers" />
    </div>
</div>
