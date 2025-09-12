<?php declare(strict_types=1);

return [
    // Document Types
    'invoice' => 'Invoice',
    'receipt' => 'Receipt',
    'contract' => 'Contract',
    'agreement' => 'Agreement',
    'catalog' => 'Catalog',
    'report' => 'Report',
    'certificate' => 'Certificate',
    'quote' => 'Quote',
    'proposal' => 'Proposal',
    'terms' => 'Terms and Conditions',

    // Document Categories
    'sales' => 'Sales',
    'marketing' => 'Marketing',
    'legal' => 'Legal',
    'finance' => 'Finance',
    'operations' => 'Operations',
    'customer_service' => 'Customer Service',
    'hr' => 'Human Resources',
    'technical' => 'Technical',

    // Document Status
    'statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
        'pending' => 'Pending Review',
    ],

    // Document Format
    'html' => 'HTML',
    'pdf' => 'PDF',
    'docx' => 'Word Document',
    'xlsx' => 'Excel Spreadsheet',

    // Document Fields
    'document_information' => 'Document Information',
    'template_information' => 'Template Information',
    'template' => 'Template',
    'title' => 'Title',
    'description' => 'Description',
    'content' => 'Content',
    'variables' => 'Variables',
    'settings' => 'Settings',
    'type' => 'Type',
    'category' => 'Category',
    'status' => 'Status',
    'format' => 'Format',
    'file_path' => 'File Path',
    'created_by' => 'Created By',
    'created_at' => 'Created At',
    'created_from' => 'Created From',
    'created_until' => 'Created Until',
    'generated_at' => 'Generated At',
    'is_active' => 'Active',
    'related_model' => 'Related Model',
    'related_model_type' => 'Related Model Type',
    'related_model_id' => 'Related Model ID',
    'metadata' => 'Metadata',
    'add_variable' => 'Add Variable',

    // Document Actions
    'generate' => 'Generate Document',
    'generate_pdf' => 'Generate PDF',
    'download' => 'Download',
    'preview' => 'Preview',
    'duplicate' => 'Duplicate',
    'archive' => 'Archive',
    'restore' => 'Restore',
    
    // Document Action Form Fields
    'title_placeholder' => 'Enter document title (optional)',
    'custom_variables' => 'Custom Variables',
    'custom_variables_placeholder' => 'VARIABLE_NAME=value' . "\n" . 'ANOTHER_VARIABLE=another value',
    'custom_variables_help' => 'Add custom variables in format: VARIABLE_NAME=value (one per line)',
    'generate_pdf_help' => 'Generate PDF version of the document',
    
    // Document Action Messages
    'document_generated_successfully' => 'Document Generated Successfully',
    'document_ready_for_review' => 'Your document has been generated and is ready for review.',
    'pdf_generated_successfully' => 'PDF Generated Successfully',
    'pdf_ready_for_download' => 'Your PDF document is ready for download.',
    'generation_failed' => 'Document Generation Failed',

    // Variables
    'available_variables' => 'Available Variables',
    'variable_name' => 'Variable Name',
    'variable_description' => 'Description',
    'variable_value' => 'Value',
    'variable_help' => 'Use variables like $CUSTOMER_NAME, $ORDER_TOTAL in your template content',

    // Print Settings
    'print_settings' => 'Print Settings',
    'page_size' => 'Page Size',
    'orientation' => 'Orientation',
    'margins' => 'Margins',
    'header' => 'Header',
    'footer' => 'Footer',
    'css' => 'Custom CSS',

    // Page Settings
    'portrait' => 'Portrait',
    'landscape' => 'Landscape',
    'a4' => 'A4',
    'a3' => 'A3',
    'letter' => 'Letter',
    'legal' => 'Legal',

    // Messages
    'document_generated' => 'Document generated successfully',
    'document_deleted' => 'Document deleted successfully',
    'template_created' => 'Template created successfully',
    'template_updated' => 'Template updated successfully',
    'template_deleted' => 'Template deleted successfully',
    'pdf_generated' => 'PDF generated successfully',
    'generation_failed' => 'Document generation failed',

    // Common Variables
    'yes' => 'Yes',
    'no' => 'No',
    'true' => 'True',
    'false' => 'False',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',

    // Help Text
    'template_help' => 'Use HTML content with variables like $CUSTOMER_NAME, $ORDER_TOTAL',
    'variables_help' => 'Define variables that can be used in document templates',
    'settings_help' => 'Configure print settings for PDF generation',

    // Email Notifications
    'email' => [
        'subject' => 'Document Generated: :title',
        'greeting' => 'Hello :name,',
        'generated' => 'Your :type document ":title" has been generated successfully.',
        'details' => 'Generated on :date with status: :status',
        'view_document' => 'View Document',
        'footer' => 'Thank you for using our document generation service.',
    ],

    // Database Notifications
    'notification' => [
        'generated' => 'Document ":title" has been generated',
    ],

    // Error Messages
    'errors' => [
        'dangerous_content' => 'Template contains potentially dangerous content',
        'generation_failed' => 'Failed to generate document',
        'template_not_found' => 'Template not found',
        'invalid_variables' => 'Invalid variables provided',
    ],
];