<?php

namespace Database\Seeders;

use App\Models\EnumValue;
use Illuminate\Database\Seeder;

class EnumValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Navigation Groups
        $navigationGroups = [
            ['type' => 'navigation_group', 'key' => 'products', 'value' => 'Products', 'name' => 'Products', 'sort_order' => 1, 'is_active' => true, 'is_default' => false],
            ['type' => 'navigation_group', 'key' => 'orders', 'value' => 'Orders', 'name' => 'Orders', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'navigation_group', 'key' => 'customers', 'value' => 'Customers', 'name' => 'Customers', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'navigation_group', 'key' => 'marketing', 'value' => 'Marketing', 'name' => 'Marketing', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['type' => 'navigation_group', 'key' => 'reports', 'value' => 'Reports', 'name' => 'Reports', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
            ['type' => 'navigation_group', 'key' => 'system', 'value' => 'System', 'name' => 'System', 'sort_order' => 6, 'is_active' => true, 'is_default' => false],
        ];

        // Order Statuses
        $orderStatuses = [
            ['type' => 'order_status', 'key' => 'pending', 'value' => 'Pending', 'name' => 'Pending Order', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'order_status', 'key' => 'processing', 'value' => 'Processing', 'name' => 'Processing Order', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'order_status', 'key' => 'shipped', 'value' => 'Shipped', 'name' => 'Shipped Order', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'order_status', 'key' => 'delivered', 'value' => 'Delivered', 'name' => 'Delivered Order', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['type' => 'order_status', 'key' => 'cancelled', 'value' => 'Cancelled', 'name' => 'Cancelled Order', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
            ['type' => 'order_status', 'key' => 'refunded', 'value' => 'Refunded', 'name' => 'Refunded Order', 'sort_order' => 6, 'is_active' => true, 'is_default' => false],
        ];

        // Payment Statuses
        $paymentStatuses = [
            ['type' => 'payment_status', 'key' => 'pending', 'value' => 'Pending', 'name' => 'Pending Payment', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'payment_status', 'key' => 'paid', 'value' => 'Paid', 'name' => 'Payment Completed', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'payment_status', 'key' => 'failed', 'value' => 'Failed', 'name' => 'Payment Failed', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'payment_status', 'key' => 'refunded', 'value' => 'Refunded', 'name' => 'Payment Refunded', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['type' => 'payment_status', 'key' => 'partially_refunded', 'value' => 'Partially Refunded', 'name' => 'Partially Refunded', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
        ];

        // Shipping Statuses
        $shippingStatuses = [
            ['type' => 'shipping_status', 'key' => 'pending', 'value' => 'Pending', 'name' => 'Pending Shipping', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'shipping_status', 'key' => 'preparing', 'value' => 'Preparing', 'name' => 'Preparing for Shipment', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'shipping_status', 'key' => 'shipped', 'value' => 'Shipped', 'name' => 'Shipped', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'shipping_status', 'key' => 'in_transit', 'value' => 'In Transit', 'name' => 'In Transit', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['type' => 'shipping_status', 'key' => 'delivered', 'value' => 'Delivered', 'name' => 'Delivered', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
            ['type' => 'shipping_status', 'key' => 'returned', 'value' => 'Returned', 'name' => 'Returned', 'sort_order' => 6, 'is_active' => true, 'is_default' => false],
        ];

        // User Roles
        $userRoles = [
            ['type' => 'user_role', 'key' => 'admin', 'value' => 'Administrator', 'name' => 'Administrator', 'sort_order' => 1, 'is_active' => true, 'is_default' => false],
            ['type' => 'user_role', 'key' => 'manager', 'value' => 'Manager', 'name' => 'Manager', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'user_role', 'key' => 'employee', 'value' => 'Employee', 'name' => 'Employee', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'user_role', 'key' => 'customer', 'value' => 'Customer', 'name' => 'Customer', 'sort_order' => 4, 'is_active' => true, 'is_default' => true],
        ];

        // Product Statuses
        $productStatuses = [
            ['type' => 'product_status', 'key' => 'active', 'value' => 'Active', 'name' => 'Active Product', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'product_status', 'key' => 'inactive', 'value' => 'Inactive', 'name' => 'Inactive Product', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'product_status', 'key' => 'draft', 'value' => 'Draft', 'name' => 'Draft Product', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'product_status', 'key' => 'archived', 'value' => 'Archived', 'name' => 'Archived Product', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Campaign Types
        $campaignTypes = [
            ['type' => 'campaign_type', 'key' => 'email', 'value' => 'Email Campaign', 'name' => 'Email Campaign', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'campaign_type', 'key' => 'sms', 'value' => 'SMS Campaign', 'name' => 'SMS Campaign', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'campaign_type', 'key' => 'social', 'value' => 'Social Media', 'name' => 'Social Media Campaign', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'campaign_type', 'key' => 'display', 'value' => 'Display Ads', 'name' => 'Display Advertising', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Discount Types
        $discountTypes = [
            ['type' => 'discount_type', 'key' => 'percentage', 'value' => 'Percentage', 'name' => 'Percentage Discount', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'discount_type', 'key' => 'fixed', 'value' => 'Fixed Amount', 'name' => 'Fixed Amount Discount', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'discount_type', 'key' => 'free_shipping', 'value' => 'Free Shipping', 'name' => 'Free Shipping', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'discount_type', 'key' => 'buy_one_get_one', 'value' => 'Buy One Get One', 'name' => 'Buy One Get One', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Notification Types
        $notificationTypes = [
            ['type' => 'notification_type', 'key' => 'order', 'value' => 'Order', 'name' => 'Order Notification', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'notification_type', 'key' => 'product', 'value' => 'Product', 'name' => 'Product Notification', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'notification_type', 'key' => 'user', 'value' => 'User', 'name' => 'User Notification', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'notification_type', 'key' => 'system', 'value' => 'System', 'name' => 'System Notification', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
            ['type' => 'notification_type', 'key' => 'payment', 'value' => 'Payment', 'name' => 'Payment Notification', 'sort_order' => 5, 'is_active' => true, 'is_default' => false],
            ['type' => 'notification_type', 'key' => 'shipping', 'value' => 'Shipping', 'name' => 'Shipping Notification', 'sort_order' => 6, 'is_active' => true, 'is_default' => false],
        ];

        // Document Types
        $documentTypes = [
            ['type' => 'document_type', 'key' => 'invoice', 'value' => 'Invoice', 'name' => 'Invoice Document', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'document_type', 'key' => 'receipt', 'value' => 'Receipt', 'name' => 'Receipt Document', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'document_type', 'key' => 'contract', 'value' => 'Contract', 'name' => 'Contract Document', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'document_type', 'key' => 'report', 'value' => 'Report', 'name' => 'Report Document', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Address Types
        $addressTypes = [
            ['type' => 'address_type', 'key' => 'billing', 'value' => 'Billing Address', 'name' => 'Billing Address', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'address_type', 'key' => 'shipping', 'value' => 'Shipping Address', 'name' => 'Shipping Address', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'address_type', 'key' => 'home', 'value' => 'Home Address', 'name' => 'Home Address', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'address_type', 'key' => 'work', 'value' => 'Work Address', 'name' => 'Work Address', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Priorities
        $priorities = [
            ['type' => 'priority', 'key' => 'low', 'value' => 'Low', 'name' => 'Low Priority', 'sort_order' => 1, 'is_active' => true, 'is_default' => false],
            ['type' => 'priority', 'key' => 'medium', 'value' => 'Medium', 'name' => 'Medium Priority', 'sort_order' => 2, 'is_active' => true, 'is_default' => true],
            ['type' => 'priority', 'key' => 'high', 'value' => 'High', 'name' => 'High Priority', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'priority', 'key' => 'urgent', 'value' => 'Urgent', 'name' => 'Urgent Priority', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Statuses
        $statuses = [
            ['type' => 'status', 'key' => 'active', 'value' => 'Active', 'name' => 'Active Status', 'sort_order' => 1, 'is_active' => true, 'is_default' => true],
            ['type' => 'status', 'key' => 'inactive', 'value' => 'Inactive', 'name' => 'Inactive Status', 'sort_order' => 2, 'is_active' => true, 'is_default' => false],
            ['type' => 'status', 'key' => 'pending', 'value' => 'Pending', 'name' => 'Pending Status', 'sort_order' => 3, 'is_active' => true, 'is_default' => false],
            ['type' => 'status', 'key' => 'completed', 'value' => 'Completed', 'name' => 'Completed Status', 'sort_order' => 4, 'is_active' => true, 'is_default' => false],
        ];

        // Combine all enum values
        $allEnumValues = array_merge(
            $navigationGroups,
            $orderStatuses,
            $paymentStatuses,
            $shippingStatuses,
            $userRoles,
            $productStatuses,
            $campaignTypes,
            $discountTypes,
            $notificationTypes,
            $documentTypes,
            $addressTypes,
            $priorities,
            $statuses
        );

        // Create enum values
        foreach ($allEnumValues as $enumValue) {
            EnumValue::create($enumValue);
        }
    }
}
