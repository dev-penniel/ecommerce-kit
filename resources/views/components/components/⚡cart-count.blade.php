<?php

use Livewire\Component;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;


new class extends Component
{
    #[Computed] 
    public function cartCount() 
    { 
        return app(CartService::class)->count(); 
        
    }

    #[On(['cart-item-added', 'cart-cleared', 'cart-updated'])] 
    public function refreshCartCount() 
    { 
        unset($this->cartCount);
        
    }
};
?>

<div>
    @if ($this->cartCount > 0)
        <span
            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-zinc-950 ring-2 ring-[#fafaf9]"
        >
            {{ $this->cartCount }}
        </span>
    @endif
</div>