<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Orders\Detail;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows generated invoices in account order detail page', function (): void {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->getKey(),
        'number'  => 'ORD-DETAIL-INV-1',
    ]);

    $invoiceTypes = ['sf', 'psf', 'isf', 'ipsf', 'ksf', 'kpsf'];

    foreach ($invoiceTypes as $index => $type) {
        OrderInvoice::factory()->create([
            'order_id'     => $order->getKey(),
            'invoice_type' => $type,
            'full_number'  => strtoupper($type) . '-00' . ($index + 1),
            'status'       => OrderInvoice::STATUS_READY,
            'is_current'   => $type === 'kpsf',
            'generated_at' => now()->subMinutes($index),
            'created_at'   => now()->subMinutes($index),
            'updated_at'   => now()->subMinutes($index),
        ]);
    }

    Livewire::actingAs($user)
        ->test(Detail::class, ['number' => 'ORD-DETAIL-INV-1'])
        ->assertSuccessful()
        ->assertSee(__('frontend.account.documents_table.title'))
        ->assertSee(__('enums.invoice_type.sf'))
        ->assertSee(__('enums.invoice_type.psf'))
        ->assertSee(__('enums.invoice_type.isf'))
        ->assertSee(__('enums.invoice_type.ipsf'))
        ->assertSee(__('enums.invoice_type.ksf'))
        ->assertSee(__('enums.invoice_type.kpsf'))
        ->assertSee('SF-001')
        ->assertDontSee(__('frontend.account.order_detail.help_message'))
        ->assertDontSee(__('frontend.account.order_detail.contact_us'));
});
