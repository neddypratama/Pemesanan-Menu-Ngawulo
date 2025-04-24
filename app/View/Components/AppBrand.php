<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (auth()->user()->role_id !== 4) {
            return <<<'HTML'
                <a href="/dashboard" wire:navigate class="flex items-center">
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-square-3-stack-3d" class="w-6 sm:w-5 -mb-1 text-red-500" />
                            <span class="font-bold text-2xl sm:text-xl md:text-2xl lg:text-3xl 
                                        bg-gradient-to-r from-yellow-500 to-pink-300 bg-clip-text text-transparent">
                                Ngawulo
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-3 mt-2 lg:mb-6 h-[24px] sm:h-[20px]">
                        <x-icon name="s-square-3-stack-3d" class="w-6 sm:w-5 -mb-1 text-red-500" />
                    </div>
                </a>
            HTML;
        } else {
            return <<<'HTML'
                <a href="/" wire:navigate class="flex items-center">
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-square-3-stack-3d" class="w-6 sm:w-5 -mb-1 text-red-500" />
                            <span class="font-bold text-2xl sm:text-xl md:text-2xl lg:text-3xl 
                                        bg-gradient-to-r from-yellow-500 to-pink-300 bg-clip-text text-transparent">
                                Ngawulo
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-3 mt-2 lg:mb-6 h-[24px] sm:h-[20px]">
                        <x-icon name="s-square-3-stack-3d" class="w-6 sm:w-5 -mb-1 text-red-500" />
                    </div>
                </a>
            HTML;
        }
    }
}
