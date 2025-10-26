<?php

declare(strict_types=1);

return [
    'name'                         => 'Name',
    'code'                         => 'Code',
    'slug'                         => 'Slug',
    'description'                  => 'Description',
    'color'                        => 'Color',
    'icon'                         => 'Icon',
    'discount_percentage'          => 'Discount Percentage',
    'discount_percentage_help'     => 'Percentage discount applied to members of this group.',
    'discount_fixed'               => 'Fixed Discount',
    'discount_fixed_help'          => 'Fixed euro amount discount applied at checkout.',
    'minimum_order_amount'         => 'Minimum Order Amount',
    'credit_limit'                 => 'Credit Limit',
    'payment_terms'                => 'Payment Terms',
    'payment_terms_due_on_receipt' => 'Due on receipt',
    'payment_terms_net_15'         => 'Net 15 days',
    'payment_terms_net_30'         => 'Net 30 days',
    'payment_terms_net_45'         => 'Net 45 days',
    'payment_terms_net_60'         => 'Net 60 days',
    'is_enabled'                   => 'Enabled',
    'is_active'                    => 'Active',
    'is_default'                   => 'Default',
    'has_special_pricing'          => 'Special pricing',
    'has_volume_discounts'         => 'Volume discounts',
    'can_view_prices'              => 'Can view prices',
    'can_place_orders'             => 'Can place orders',
    'can_view_catalog'             => 'Can view catalog',
    'can_use_coupons'              => 'Can use coupons',
    'sort_order'                   => 'Sort order',
    'type'                         => 'Type',
    'conditions'                   => 'Conditions',
    'users_count'                  => 'Users Count',
    'created_at'                   => 'Created At',
    'updated_at'                   => 'Updated At',

    // Section titles
    'basic_information' => 'Basic Information',
    'pricing_settings'  => 'Pricing Settings',
    'permissions'       => 'Permissions',
    'settings'          => 'Settings',

    // Navigation
    'navigation_label' => 'Customer Groups',
    'navigation_group' => 'Customer Management',

    // Table columns
    'table_name'                => 'Name',
    'table_slug'                => 'Slug',
    'table_description'         => 'Description',
    'table_discount_percentage' => 'Discount %',
    'table_is_enabled'          => 'Enabled',
    'table_users_count'         => 'Users',
    'table_created_at'          => 'Created',
    'table_updated_at'          => 'Updated',

    // Filters
    'filter_enabled'           => 'Enabled',
    'filter_with_discount'     => 'With Discount',
    'filter_discount_range'    => 'Discount Range',
    'filter_users_count_range' => 'Users Count Range',
    'filter_created_date'      => 'Created Date',

    // Actions
    'action_view'         => 'View',
    'action_edit'         => 'Edit',
    'action_delete'       => 'Delete',
    'action_create'       => 'Create New',
    'activate'            => 'Activate',
    'deactivate'          => 'Deactivate',
    'set_default'         => 'Set as Default',
    'activate_selected'   => 'Activate Selected',
    'deactivate_selected' => 'Deactivate Selected',

    // Messages
    'created_successfully'        => 'Customer group created successfully',
    'updated_successfully'        => 'Customer group updated successfully',
    'deleted_successfully'        => 'Customer group deleted successfully',
    'activated_successfully'      => 'Customer group activated successfully',
    'deactivated_successfully'    => 'Customer group deactivated successfully',
    'set_as_default_successfully' => 'Customer group set as default successfully',
    'bulk_activated_success'      => 'Selected customer groups activated successfully',
    'bulk_deactivated_success'    => 'Selected customer groups deactivated successfully',

    // Widgets
    'widget_total_groups'         => 'Total Groups',
    'widget_active_groups'        => 'Active Groups',
    'widget_groups_with_discount' => 'Groups with Discount',
    'widget_total_customers'      => 'Total Customers',
    'widget_average_discount'     => 'Average Discount',

    // Relations
    'relation_users'       => 'Users',
    'relation_discounts'   => 'Discounts',
    'relation_price_lists' => 'Price Lists',
    'relation_campaigns'   => 'Campaigns',

    // Form validation
    'validation_name_required'               => 'Name is required',
    'validation_slug_required'               => 'Slug is required',
    'validation_slug_unique'                 => 'Slug already exists',
    'validation_discount_percentage_numeric' => 'Discount percentage must be a number',
    'validation_discount_percentage_min'     => 'Discount percentage cannot be less than 0',
    'validation_discount_percentage_max'     => 'Discount percentage cannot be greater than 100',

    // Additional translations
    'no_discount'   => 'No discount',
    'all_groups'    => 'All groups',
    'enabled_only'  => 'Enabled only',
    'disabled_only' => 'Disabled only',
    'discount_from' => 'Discount from',
    'discount_to'   => 'Discount to',
    'users_from'    => 'Users from',
    'users_to'      => 'Users to',

    // Relation actions
    'attach_user'       => 'Attach user',
    'detach_user'       => 'Detach user',
    'attach_discount'   => 'Attach discount',
    'detach_discount'   => 'Detach discount',
    'attach_price_list' => 'Attach price list',
    'detach_price_list' => 'Detach price list',
    'attach_campaign'   => 'Attach campaign',
    'detach_campaign'   => 'Detach campaign',
    'detach_selected'   => 'Detach selected',
];
