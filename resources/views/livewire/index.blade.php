<?php

use Livewire\Volt\Component;
use App\Models\Menu;
use App\Models\Kategori;
use App\Models\Cart;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.buy')] class extends Component {
    public $categories;
    public $menus;
    public string $search = '';
    public array $selectedCategories = [];
    public int $filters = 0;
    public $cartItems = [];

    public function loadCart()
    {
        $userId = Auth::id();
        if ($userId) {
            $this->cartItems = Cart::with('menu')->where('user_id', $userId)->get();
        } else {
            $this->cartItems = [];
        }
    }

    public function mount()
    {
        $this->loadCart();
        $this->categories = Kategori::all();
        $this->updateFilters();
        $this->loadMenus();
    }

    public function updatedSearch()
    {
        $this->updateFilters();
        $this->loadMenus();
    }

    public function updatedSelectedCategories()
    {
        $this->updateFilters();
        $this->loadMenus();
    }

    public function clearFilters()
    {
        $this->selectedCategories = [];
        $this->search = '';
        $this->updateFilters();
        $this->loadMenus();
    }
    private function loadMenus()
    {
        $this->menus = Menu::withAvg('ratings', 'rating')
            ->withCount('ratings') // Tambahkan ini
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->when(count($this->selectedCategories) > 0, function ($query) {
                $query->whereIn('kategori_id', $this->selectedCategories);
            })
            ->get();
    }

    private function updateFilters()
    {
        $this->filters = (strlen($this->search) > 0 ? 1 : 0) + count($this->selectedCategories);
    }

    public function goToDetail($menuId)
    {
        $this->js("window.dispatchEvent(new CustomEvent('navigate-to-detail', { detail: { menu: $menuId } }));");
    }
};
?>

<div>
    <!-- FILTERS -->
    <div class="grid grid-cols-1 md:grid-cols-9 gap-4 items-end mb-4">
        <div class="md:col-span-8">
            <x-input placeholder="Cari yang kamu inginkan..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </div>
        <div class="md:col-span-1">
            <x-dropdown class="btn-primary">
                <x-slot name="trigger">
                    <x-button class="outline-violet-500">
                        Kategori
                        @if ($filters >= 0)
                            <x-badge :value="$filters" class="ml-2 bg-indigo-600 text-white" />
                        @endif
                    </x-button>
                </x-slot>

                <x-menu-item title="Clear" wire:click="clearFilters" icon="fas.xmark" />
                <x-menu-separator />

                @foreach ($categories as $category)
                    <x-menu-item @click.stop="">
                        <x-checkbox wire:model.live="selectedCategories" value="{{ $category->id }}"
                            label="{{ $category->name }}" />
                    </x-menu-item>
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <!-- Daftar Menu -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        @forelse ($menus as $menu)
            <x-card title="{{ $menu->name }}" class="cursor-pointer hover:shadow-lg transition"
                wire:click="goToDetail({{ $menu->id }})">
                @php
                    $averageRating = round($menu->ratings_avg_rating ?? 0); // dari withAvg
                    $totalRatings = $menu->ratings_count ?? 0;
                @endphp
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $averageRating)
                            <x-icon name="fas.star" class="w-5 h-5 text-yellow-400" />
                        @else
                            <x-icon name="fas.star" class="w-5 h-5 text-gray-300" />
                        @endif
                    @endfor
                    <span class="text-sm text-gray-500 ml-2">({{ $totalRatings }})</span>
                </div>

                <x-slot:figure>
                    <img src="{{ $menu->photo }}" alt="{{ $menu->name }}" class="w-full h-40 object-cover rounded">
                </x-slot:figure>
                <x-slot:menu>
                    Rp. {{ number_format($menu->price, 0, ',', '.') }}
                </x-slot:menu>
            </x-card>
        @empty
            <p class="col-span-full text-center">Menu tidak ditemukan.</p>
        @endforelse
    </div>
</div>

<script>
    window.addEventListener('navigate-to-detail', event => {
        const id = event.detail.menu;
        console.log(id);
        window.Livewire.navigate(`/detail/${id}`);
    });
</script>
