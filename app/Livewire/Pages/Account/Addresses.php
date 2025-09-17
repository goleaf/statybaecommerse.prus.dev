<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Account;

use App\Models\Address;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * Addresses
 * 
 * Livewire component for Addresses with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
#[Layout('components.layouts.templates.account')]
class Addresses extends Component
{
    /**
     * Handle removeAddress functionality with proper error handling.
     * @param int $id
     * @return void
     */
    public function removeAddress(int $id): void
    {
        Address::query()->find($id)->delete();
        Notification::make()->title(__('The address has been correctly removed from your list!'))->success()->send();
        $this->dispatch('addresses-updated');
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    #[On('addresses-updated')]
    public function render(): View
    {
        return view('livewire.pages.account.addresses', ['addresses' => auth()->user()->addresses->load('country')])->title(__('My addresses'));
    }
}