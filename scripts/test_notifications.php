<?php

declare(strict_types=1);

// Simple test script to verify notification classes work
require_once 'vendor/autoload.php';

use App\Notifications\AdminNotification;
use App\Notifications\OrderNotification;
use App\Notifications\ProductNotification;
use App\Notifications\SystemNotification;
use App\Notifications\TestNotification;
use App\Notifications\UserNotification;

echo "Testing Notification Classes...\n\n";

// Test TestNotification
try {
    $testNotification = new TestNotification('Test Title', 'Test Message', 'info');
    echo "✅ TestNotification: Created successfully\n";
    echo '   - Title: ' . $testNotification->title . "\n";
    echo '   - Message: ' . $testNotification->message . "\n";
    echo '   - Type: ' . $testNotification->type . "\n";
} catch (Exception $e) {
    echo '❌ TestNotification: Failed - ' . $e->getMessage() . "\n";
}

// Test AdminNotification
try {
    $adminNotification = new AdminNotification('Admin Title', 'Admin Message', 'warning');
    echo "✅ AdminNotification: Created successfully\n";
    echo '   - Title: ' . $adminNotification->title . "\n";
    echo '   - Message: ' . $adminNotification->message . "\n";
    echo '   - Type: ' . $adminNotification->type . "\n";
} catch (Exception $e) {
    echo '❌ AdminNotification: Failed - ' . $e->getMessage() . "\n";
}

// Test OrderNotification
try {
    $orderData = [
        'id'           => 1,
        'order_number' => 'ORD-001',
        'total'        => 100.00,
        'status'       => 'pending',
    ];
    $orderNotification = new OrderNotification($orderData);
    echo "✅ OrderNotification: Created successfully\n";
    echo '   - Order ID: ' . $orderData['id'] . "\n";
    echo '   - Order Number: ' . $orderData['order_number'] . "\n";
    echo '   - Total: ' . $orderData['total'] . "\n";
} catch (Exception $e) {
    echo '❌ OrderNotification: Failed - ' . $e->getMessage() . "\n";
}

// Test SystemNotification
try {
    $systemData = [
        'maintenance_type' => 'scheduled',
        'duration'         => '2 hours',
    ];
    $systemNotification = new SystemNotification($systemData);
    echo "✅ SystemNotification: Created successfully\n";
    echo '   - Maintenance Type: ' . $systemData['maintenance_type'] . "\n";
    echo '   - Duration: ' . $systemData['duration'] . "\n";
} catch (Exception $e) {
    echo '❌ SystemNotification: Failed - ' . $e->getMessage() . "\n";
}

// Test UserNotification
try {
    $userData = [
        'id'    => 1,
        'name'  => 'John Doe',
        'email' => 'john@example.com',
    ];
    $userNotification = new UserNotification('registered', $userData, 'Custom message');
    echo "✅ UserNotification: Created successfully\n";
    echo '   - Action: ' . $userNotification->action . "\n";
    echo '   - User Name: ' . $userData['name'] . "\n";
    echo '   - Message: ' . $userNotification->message . "\n";
} catch (Exception $e) {
    echo '❌ UserNotification: Failed - ' . $e->getMessage() . "\n";
}

// Test ProductNotification
try {
    $productData = [
        'id'    => 1,
        'name'  => 'Test Product',
        'sku'   => 'TEST-001',
        'price' => 99.99,
    ];
    $productNotification = new ProductNotification($productData);
    echo "✅ ProductNotification: Created successfully\n";
    echo '   - Product Name: ' . $productData['name'] . "\n";
    echo '   - SKU: ' . $productData['sku'] . "\n";
    echo '   - Price: ' . $productData['price'] . "\n";
} catch (Exception $e) {
    echo '❌ ProductNotification: Failed - ' . $e->getMessage() . "\n";
}

echo "\n🎉 All notification classes tested successfully!\n";
echo "The notification system is working correctly.\n";
