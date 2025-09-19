<?php

return [
    'title' => 'Campaign Schedules',
    'plural' => 'Campaign Schedules',
    'single' => 'Campaign Schedule',
    
    'form' => [
        'tabs' => [
            'basic_information' => 'Basic Information',
            'schedule_config' => 'Schedule Configuration',
            'campaign_details' => 'Campaign Details',
        ],
        'sections' => [
            'basic_information' => 'Basic Information',
            'schedule_config' => 'Schedule Configuration',
            'campaign_details' => 'Campaign Details',
        ],
        'fields' => [
            'campaign' => 'Campaign',
            'schedule_type' => 'Schedule Type',
            'next_run_at' => 'Next Run At',
            'last_run_at' => 'Last Run At',
            'is_active' => 'Is Active',
            'schedule_config' => 'Schedule Configuration',
            'config_key' => 'Config Key',
            'config_value' => 'Config Value',
            'campaign_name' => 'Campaign Name',
            'campaign_status' => 'Campaign Status',
            'campaign_type' => 'Campaign Type',
            'schedule_status' => 'Schedule Status',
        ],
    ],
    
    'schedule_types' => [
        'once' => 'Once',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'custom' => 'Custom',
    ],
    
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'scheduled' => 'Scheduled',
        'ready' => 'Ready',
    ],
    
    'filters' => [
        'campaign' => 'Campaign',
        'schedule_type' => 'Schedule Type',
        'is_active' => 'Is Active',
        'next_run_at' => 'Next Run At',
        'last_run_at' => 'Last Run At',
        'overdue' => 'Overdue',
    ],
    
    'actions' => [
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'run_now' => 'Run Now',
        'activate_bulk' => 'Bulk Activate',
        'deactivate_bulk' => 'Bulk Deactivate',
    ],
    
    'notifications' => [
        'activated_successfully' => 'Activated successfully',
        'deactivated_successfully' => 'Deactivated successfully',
        'run_successfully' => 'Run successfully',
        'bulk_activated_successfully' => 'Bulk activated successfully',
        'bulk_deactivated_successfully' => 'Bulk deactivated successfully',
    ],
];

