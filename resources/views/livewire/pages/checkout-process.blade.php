<div class="grid gap-8 lg:grid-cols-[1fr_360px]">
  <div>
    <x-checkout.steps :current="$currentStep"/>

    @if ($currentStep === 'billing')
      <x-checkout.billing-form />
    @elseif ($currentStep === 'shipping')
      @livewire('components.checkout.delivery')
    @elseif ($currentStep === 'payment')
      @livewire('components.checkout.payment')
    @endif

    <div class="mt-6 flex justify-between">
      <button class="btn" wire:click="prev" @disabled($isFirstStep)>{{ __('Back') }}</button>
      <button class="btn btn-primary" wire:click="next" @disabled($isLastStep)>{{ __('Next') }}</button>
    </div>
  </div>

  <aside class="space-y-4">
    <x-checkout.order-summary :items="$summary['items'] ?? []"
                              :subTotal="$summary['sub_total'] ?? 0"
                              :shipping="$summary['shipping'] ?? 0"
                              :total="$summary['total'] ?? 0" />
  </aside>
</div>
