<?php declare(strict_types=1);

return [
    // General
    'documents' => 'Documents',
    'document' => 'Document',
    'templates' => 'Templates',
    'template' => 'Template',
    'generate_document' => 'Generate Document',
    'document_generated' => 'Document generated successfully',
    'generate' => 'Generate',
    'generate_pdf' => 'Generate PDF',
    'download' => 'Download',
    'yes' => 'Yes',
    'no' => 'No',

    // Fields
    'name' => 'Name',
    'slug' => 'Slug',
    'title' => 'Title',
    'description' => 'Description',
    'content' => 'Content',
    'type' => 'Type',
    'category' => 'Category',
    'status' => 'Status',
    'format' => 'Format',
    'variables' => 'Variables',
    'settings' => 'Settings',
    'is_active' => 'Active',
    'created_at' => 'Created At',
    'created_by' => 'Created By',
    'generated_at' => 'Generated At',
    'file_path' => 'File Path',
    'notes' => 'Notes',
    'documents_count' => 'Documents Count',

    // Types
    'types' => [
        'invoice' => 'Invoice',
        'receipt' => 'Receipt',
        'contract' => 'Contract',
        'agreement' => 'Agreement',
        'catalog' => 'Catalog',
        'report' => 'Report',
        'certificate' => 'Certificate',
        'document' => 'Document',
    ],

    // Categories
    'categories' => [
        'sales' => 'Sales',
        'marketing' => 'Marketing',
        'legal' => 'Legal',
        'finance' => 'Finance',
        'operations' => 'Operations',
        'customer_service' => 'Customer Service',
    ],

    // Statuses
    'statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    // Sections
    'template_information' => 'Template Information',
    'template_content' => 'Template Content',
    'print_settings' => 'Print Settings',
    'document_information' => 'Document Information',
    'metadata' => 'Metadata',

    // Form fields
    'variable_name' => 'Variable Name',
    'variable_value' => 'Variable Value',
    'add_variable' => 'Add Variable',
    'setting_key' => 'Setting Key',
    'setting_value' => 'Setting Value',
    'add_setting' => 'Add Setting',
    'related_model' => 'Related Model',
    'related_model_type' => 'Related Model Type',
    'related_model_id' => 'Related Model ID',
    'created_from' => 'Created From',
    'created_until' => 'Created Until',

    // Help texts
    'content_help' => 'Use variables like $CUSTOMER_NAME, $ORDER_TOTAL in your content. They will be replaced with actual values when generating documents.',
    'variables_help' => 'Define available variables for this template. Use format: $VARIABLE_NAME',
    
    // Print-specific
    'phone' => 'Phone',
    'email' => 'Email',
    'vat_number' => 'VAT Number',
    'generated_on' => 'Generated on',
    'all_rights_reserved' => 'All rights reserved',
];
