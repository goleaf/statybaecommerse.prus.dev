<?php

return [
    // Resource labels
    'single' => 'Customer Group',
    'plural' => 'Customer Groups',

    // Fields
    'name' => 'Name',
    'code' => 'Code',
    'slug' => 'Slug',
    'description' => 'Description',
    'discount_percentage' => 'Discount Percentage',
    'discount_percentage_help' => 'Set a percentage discount for this group.',
    'discount_fixed' => 'Fixed Discount',
    'discount_fixed_help' => 'Set a fixed discount amount for this group.',
    'has_special_pricing' => 'Special Pricing',
    'has_volume_discounts' => 'Volume Discounts',
    'can_view_prices' => 'Can View Prices',
    'can_place_orders' => 'Can Place Orders',
    'can_view_catalog' => 'Can View Catalog',
    'can_use_coupons' => 'Can Use Coupons',
    'is_enabled' => 'Enabled',
    'is_active' => 'Active',
    'is_default' => 'Is Default',
    'sort_order' => 'Sort Order',
    'type' => 'Type',
    'customers_count' => 'Customer Count',
    'conditions' => 'Conditions',
    'users_count' => 'Users Count',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Section titles
    'basic_information' => 'Basic Information',
    'pricing_settings' => 'Pricing Settings',
    'permissions' => 'Permissions',
    'settings' => 'Settings',

    // Navigation
    'navigation_label' => 'Customer Groups',
    'navigation_group' => 'Customer Management',

    // Table columns
    'table_name' => 'Name',
    'table_slug' => 'Slug',
    'table_description' => 'Description',
    'table_code' => 'Code',
    'table_discount_percentage' => 'Discount %',
    'table_is_enabled' => 'Enabled',
    'table_customers_count' => 'Customers',
    'table_users_count' => 'Users',
    'table_created_at' => 'Created',
    'table_updated_at' => 'Updated',

    // Filters
    'filter_enabled' => 'Enabled',
    'filter_with_discount' => 'With Discount',
    'filter_discount_range' => 'Discount Range',
    'filter_users_count_range' => 'Users Count Range',
    'filter_created_date' => 'Created Date',
    'active_only' => 'Active Only',
    'inactive_only' => 'Inactive Only',
    'default_only' => 'Default Only',
    'non_default_only' => 'Non-Default Only',
    'special_pricing_only' => 'Special Pricing Only',
    'no_special_pricing' => 'Without Special Pricing',
    'volume_discounts_only' => 'Volume Discounts Only',
    'no_volume_discounts' => 'Without Volume Discounts',

    // Actions
    'action_view' => 'View',
    'action_edit' => 'Edit',
    'action_delete' => 'Delete',
    'action_create' => 'Create New',
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'set_default' => 'Set as Default',
    'activate_selected' => 'Activate Selected',
    'deactivate_selected' => 'Deactivate Selected',

    // Messages
    'created_successfully' => 'Customer group created successfully',
    'updated_successfully' => 'Customer group updated successfully',
    'deleted_successfully' => 'Customer group deleted successfully',
    'activated_successfully' => 'Customer group activated successfully',
    'deactivated_successfully' => 'Customer group deactivated successfully',
    'set_as_default_successfully' => 'Customer group set as default successfully',
    'bulk_activated_success' => 'Selected customer groups activated successfully',
    'bulk_deactivated_success' => 'Selected customer groups deactivated successfully',

    // Widgets
    'widget_total_groups' => 'Total Groups',
    'widget_active_groups' => 'Active Groups',
    'widget_groups_with_discount' => 'Groups with Discount',
    'widget_total_customers' => 'Total Customers',
    'widget_average_discount' => 'Average Discount',

    // Relations
    'relation_users' => 'Users',
    'relation_discounts' => 'Discounts',
    'relation_price_lists' => 'Price Lists',
    'relation_campaigns' => 'Campaigns',

    // Form validation
    'validation_name_required' => 'Name is required',
    'validation_slug_required' => 'Slug is required',
    'validation_slug_unique' => 'Slug already exists',
    'validation_discount_percentage_numeric' => 'Discount percentage must be a number',
    'validation_discount_percentage_min' => 'Discount percentage cannot be less than 0',
    'validation_discount_percentage_max' => 'Discount percentage cannot be greater than 100',

    // Additional translations
    'types' => [
        'regular' => 'Regular',
        'vip' => 'VIP',
        'wholesale' => 'Wholesale',
        'retail' => 'Retail',
        'corporate' => 'Corporate',
    ],
    'no_discount' => 'No discount',
    'all_groups' => 'All groups',
    'enabled_only' => 'Enabled only',
    'disabled_only' => 'Disabled only',
    'discount_from' => 'Discount from',
    'discount_to' => 'Discount to',
    'users_from' => 'Users from',
    'users_to' => 'Users to',

    // Relation actions
    'attach_user' => 'Attach user',
    'detach_user' => 'Detach user',
    'attach_discount' => 'Attach discount',
    'detach_discount' => 'Detach discount',
    'attach_price_list' => 'Attach price list',
    'detach_price_list' => 'Detach price list',
    'attach_campaign' => 'Attach campaign',
    'detach_campaign' => 'Detach campaign',
    'detach_selected' => 'Detach selected',
];
