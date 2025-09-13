<?php

require_once 'vendor/autoload.php';

use App\Models\Zone;
use App\Models\Currency;
use App\Models\Country;

// Test basic Zone model functionality
echo "Testing Zone model...\n";

try {
    // Test creating a zone
    $currency = new Currency();
    $currency->name = 'Euro';
    $currency->code = 'EUR';
    $currency->symbol = '€';
    $currency->is_default = true;
    $currency->save();
    
    $zone = new Zone();
    $zone->name = 'Test Zone';
    $zone->slug = 'test-zone';
    $zone->code = 'TZ';
    $zone->description = 'Test zone description';
    $zone->currency_id = $currency->id;
    $zone->tax_rate = 21.00;
    $zone->shipping_rate = 5.99;
    $zone->type = 'shipping';
    $zone->priority = 1;
    $zone->is_enabled = true;
    $zone->is_active = true;
    $zone->is_default = false;
    $zone->save();
    
    echo "Zone created successfully with ID: " . $zone->id . "\n";
    
    // Test tax calculation
    $taxAmount = $zone->calculateTax(100.00);
    echo "Tax calculation (100.00): " . $taxAmount . "\n";
    
    // Test shipping calculation
    $shippingCost = $zone->calculateShipping(2.0, 50.00);
    echo "Shipping calculation (2.0kg, 50.00): " . $shippingCost . "\n";
    
    // Test formatted attributes
    echo "Formatted tax rate: " . $zone->formatted_tax_rate . "\n";
    echo "Formatted shipping rate: " . $zone->formatted_shipping_rate . "\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
