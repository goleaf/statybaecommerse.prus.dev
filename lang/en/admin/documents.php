<?php

return [
    'title' => 'Documents',
    'plural' => 'Documents',
    'single' => 'Document',
    'form' => [
        'tabs' => [
            'basic_information' => 'Basic Information',
            'variables' => 'Variables',
            'organization' => 'Organization',
            'file_management' => 'File Management',
        ],
        'sections' => [
            'basic_information' => 'Basic Information',
            'variables' => 'Variables',
            'organization' => 'Organization',
            'file_management' => 'File Management',
        ],
        'fields' => [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'status' => 'Status',
            'format' => 'Format',
            'template' => 'Template',
            'variables' => 'Variables',
            'variable_name' => 'Variable Name',
            'variable_value' => 'Variable Value',
            'documentable_type' => 'Related Model Type',
            'documentable_id' => 'Related Model ID',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'generated_at' => 'Generated At',
            'file_path' => 'File Path',
            'file_attached' => 'File Attached',
            'is_public' => 'Is Public',
        ],
        'actions' => [
            'add_variable' => 'Add Variable',
        ],
        'help' => [
            'variables' => 'Variables can be used in the document content. Use the format {{variable_name}} in your content.',
        ],
    ],
    'status' => [
        'draft' => 'Draft',
        'generated' => 'Generated',
        'published' => 'Published',
        'archived' => 'Archived',
    ],
    'actions' => [
        'generate' => 'Generate',
        'publish' => 'Publish',
        'archive' => 'Archive',
        'download' => 'Download',
    ],
    'filters' => [
        'is_generated' => 'Is Generated',
        'has_file' => 'Has File',
        'created_at' => 'Created At',
        'generated_at' => 'Generated At',
        'recent' => 'Recent (Last 7 days)',
        'old_documents' => 'Old Documents (30+ days)',
    ],
    'groups' => [
        'status' => 'Status',
        'format' => 'Format',
        'template' => 'Template',
    ],
    'generated_successfully' => 'Document generated successfully',
    'published_successfully' => 'Document published successfully',
    'archived_successfully' => 'Document archived successfully',
];
