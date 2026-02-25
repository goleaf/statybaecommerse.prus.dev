<?php

declare(strict_types=1);

use App\Models\Order;
use App\Services\VenipakService;
use Illuminate\Support\Facades\Http;

it('fetches pickup points successfully', function () {
    Http::fake([
        'go.venipak.lt/ws/get_pickup_points*' => Http::response([
            ['id' => '1', 'name' => 'Locker 1', 'address' => 'Vilnius'],
            ['id' => '2', 'name' => 'Locker 2', 'address' => 'Kaunas'],
        ], 200),
    ]);

    $service = new VenipakService();
    $pickupPoints = $service->getPickupPoints('LT');

    expect($pickupPoints)->toBeArray()
        ->toHaveCount(2)
        ->and($pickupPoints[0]['name'])->toBe('Locker 1');
});

it('generates shipping labels successfully', function () {
    Http::fake([
        '*/print_label' => Http::response('mock_pdf_content', 200),
    ]);

    $service = new VenipakService();
    $pdf = $service->getLabels(['V123', 'V124']);

    expect($pdf)->toBe('mock_pdf_content');
});

it('dispatches order successfully', function () {
    config()->set('venipak.username', 'test_user');
    config()->set('venipak.password', 'test_pass');

    $order = Order::factory()->create();
    
    $service = new VenipakService();
    // Assuming simple return array simulation as defined in service
    $response = $service->dispatchOrder($order, 2);

    expect($response)
        ->toHaveKeys(['tracking_numbers', 'manifest_id'])
        ->and($response['tracking_numbers'])->toBeArray()
        ->toHaveCount(2);
});
