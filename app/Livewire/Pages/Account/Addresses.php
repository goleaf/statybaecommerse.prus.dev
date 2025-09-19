<?php

declare (strict_types=1);
namespace App\Livewire\Pages\Account;

use App\Models\Address;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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
        try {
            $address = Address::query()->findOrFail($id);
            
            // Check if user owns this address
            if ($address->user_id !== Auth::id()) {
                Notification::make()
                    ->title(__('Unauthorized'))
                    ->body(__('You are not authorized to delete this address.'))
                    ->danger()
                    ->send();
                return;
            }
            
            $address->delete();
            
            Notification::make()
                ->title(__('Address deleted successfully'))
                ->body(__('The address has been removed from your list.'))
                ->success()
                ->send();
                
            $this->dispatch('addresses-updated');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to delete address. Please try again.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Handle setDefaultAddress functionality with proper error handling.
     * @param int $id
     * @return void
     */
    public function setDefaultAddress(int $id): void
    {
        try {
            $address = Address::query()->findOrFail($id);
            
            // Check if user owns this address
            if ($address->user_id !== Auth::id()) {
                Notification::make()
                    ->title(__('Unauthorized'))
                    ->body(__('You are not authorized to modify this address.'))
                    ->danger()
                    ->send();
                return;
            }
            
            // Remove default from other addresses of the same type
            Auth::user()->addresses()
                ->where('type', $address->type)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
            
            // Set this address as default
            $address->update(['is_default' => true]);
            
            Notification::make()
                ->title(__('Default address updated'))
                ->body(__('The address has been set as your default :type address.', ['type' => $address->type]))
                ->success()
                ->send();
                
            $this->dispatch('addresses-updated');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to set default address. Please try again.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    #[On('addresses-updated')]
    public function render(): View
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        $addresses = $user->addresses()
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('livewire.pages.account.addresses', [
            'addresses' => $addresses
        ])->title(__('My addresses'));
    }
}