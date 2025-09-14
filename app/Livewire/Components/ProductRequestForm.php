<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
/**
 * ProductRequestForm
 * 
 * Livewire component for ProductRequestForm with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $message
 * @property int $requested_quantity
 * @property bool $showForm
 * @property mixed $rules
 * @property mixed $messages
 */
final class ProductRequestForm extends Component
{
    public Product $product;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';
    public int $requested_quantity = 1;
    public bool $showForm = false;
    protected $rules = ['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'phone' => 'nullable|string|max:20', 'message' => 'nullable|string|max:1000', 'requested_quantity' => 'required|integer|min:1|max:999'];
    protected $messages = ['name.required' => 'Vardas yra privalomas', 'email.required' => 'El. paštas yra privalomas', 'email.email' => 'El. paštas turi būti teisingas', 'requested_quantity.required' => 'Kiekis yra privalomas', 'requested_quantity.min' => 'Kiekis turi būti bent 1', 'requested_quantity.max' => 'Kiekis negali viršyti 999'];
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        $this->product = $product;
        // Pre-fill form if user is logged in
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
        }
    }
    /**
     * Handle toggleForm functionality with proper error handling.
     * @return void
     */
    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }
    /**
     * Handle submitRequest functionality with proper error handling.
     * @return void
     */
    public function submitRequest(): void
    {
        $this->validate();
        // Create the product request
        $request = ProductRequest::create(['product_id' => $this->product->id, 'user_id' => Auth::id(), 'name' => $this->name, 'email' => $this->email, 'phone' => $this->phone, 'message' => $this->message, 'requested_quantity' => $this->requested_quantity, 'status' => 'pending']);
        // Increment the product's request count
        $this->product->incrementRequestsCount();
        // Reset form and hide it
        $this->resetForm();
        $this->showForm = false;
        // Show success message
        session()->flash('request_success', 'Jūsų užklausa sėkmingai išsiųsta. Susisieksime su jumis artimiausiu metu.');
        // Dispatch event to refresh parent component
        $this->dispatch('request-submitted');
    }
    /**
     * Handle resetForm functionality with proper error handling.
     * @return void
     */
    private function resetForm(): void
    {
        $this->name = Auth::check() ? Auth::user()->name : '';
        $this->email = Auth::check() ? Auth::user()->email : '';
        $this->phone = Auth::check() ? Auth::user()->phone ?? '' : '';
        $this->message = '';
        $this->requested_quantity = 1;
        $this->resetErrorBag();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product-request-form');
    }
}