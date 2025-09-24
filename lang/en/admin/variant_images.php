<?php

return [
    // Navigation and Labels
    'navigation_label' => 'Variant Images',
    'plural_model_label' => 'Variant Images',
    'model_label' => 'Variant Image',

    // Form Sections
    'basic_information' => 'Basic Information',
    'image_details' => 'Image Details',
    'display_settings' => 'Display Settings',
    'metadata' => 'Metadata',

    // Form Fields
    'variant' => 'Product Variant',
    'variant_info' => 'Variant Information',
    'image' => 'Image',
    'alt_text' => 'Alt Text',
    'description' => 'Description',
    'sort_order' => 'Sort Order',
    'is_primary' => 'Is Primary',
    'is_active' => 'Is Active',
    'file_size' => 'File Size',
    'dimensions' => 'Dimensions',
    'created_by' => 'Created By',

    // Table Columns
    'sku' => 'SKU',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Help Text
    'image_help' => 'Upload an image file (JPEG, PNG, WebP). Maximum size: 5MB.',
    'alt_text_help' => 'Alternative text for accessibility and SEO.',
    'description_help' => 'Optional description of the image.',
    'sort_order_help' => 'Order in which images are displayed (lower numbers first).',
    'is_primary_help' => 'Only one image per variant can be primary.',
    'is_active_help' => 'Inactive images are hidden from the frontend.',

    // Actions
    'set_as_primary' => 'Set as Primary',
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'duplicate' => 'Duplicate',
    'reorder_images' => 'Reorder Images',

    // Bulk Actions
    'activate_selected' => 'Activate Selected',
    'deactivate_selected' => 'Deactivate Selected',
    'set_primary_selected' => 'Set Primary Selected',

    // Filters
    'primary_only' => 'Primary Only',
    'non_primary_only' => 'Non-Primary Only',
    'active_only' => 'Active Only',
    'inactive_only' => 'Inactive Only',
    'created_from' => 'Created From',
    'created_until' => 'Created Until',

    // Notifications
    'set_as_primary_successfully' => 'Image set as primary successfully.',
    'activated_successfully' => 'Image activated successfully.',
    'deactivated_successfully' => 'Image deactivated successfully.',
    'duplicated_successfully' => 'Image duplicated successfully.',
    'reordered_successfully' => 'Images reordered successfully.',
    'bulk_activated_successfully' => 'Selected images activated successfully.',
    'bulk_deactivated_successfully' => 'Selected images deactivated successfully.',
    'bulk_primary_set_successfully' => 'Primary images set successfully.',

    // Validation Messages
    'variant_required' => 'Please select a product variant.',
    'image_required' => 'Please upload an image.',
    'image_invalid_format' => 'Invalid image format. Please upload a JPEG, PNG, or WebP file.',
    'image_too_large' => 'Image file is too large. Maximum size is 5MB.',
    'alt_text_max_length' => 'Alt text cannot exceed 255 characters.',
    'description_max_length' => 'Description cannot exceed 1000 characters.',
    'sort_order_numeric' => 'Sort order must be a number.',
    'sort_order_min' => 'Sort order must be at least 0.',

    // Status Messages
    'no_images_found' => 'No variant images found.',
    'no_primary_image' => 'No primary image set for this variant.',
    'multiple_primary_images' => 'Multiple primary images detected. Only one should be primary.',
    'image_not_found' => 'Image file not found.',
    'image_upload_failed' => 'Failed to upload image.',
    'image_delete_failed' => 'Failed to delete image file.',

    // Statistics
    'total_images' => 'Total Images',
    'primary_images' => 'Primary Images',
    'active_images' => 'Active Images',
    'inactive_images' => 'Inactive Images',
    'total_file_size' => 'Total File Size',
    'average_file_size' => 'Average File Size',

    // Image Processing
    'processing_image' => 'Processing image...',
    'image_processed' => 'Image processed successfully.',
    'image_processing_failed' => 'Failed to process image.',
    'generating_thumbnails' => 'Generating thumbnails...',
    'thumbnails_generated' => 'Thumbnails generated successfully.',
    'thumbnail_generation_failed' => 'Failed to generate thumbnails.',

    // File Management
    'file_not_found' => 'File not found.',
    'file_already_exists' => 'File already exists.',
    'file_size_exceeded' => 'File size exceeded maximum limit.',
    'invalid_file_type' => 'Invalid file type.',
    'file_corrupted' => 'File appears to be corrupted.',

    // Bulk Operations
    'bulk_operation_in_progress' => 'Bulk operation in progress...',
    'bulk_operation_completed' => 'Bulk operation completed successfully.',
    'bulk_operation_failed' => 'Bulk operation failed.',
    'select_images_first' => 'Please select images first.',
    'no_images_selected' => 'No images selected.',
    'confirm_bulk_delete' => 'Are you sure you want to delete the selected images?',
    'confirm_bulk_activate' => 'Are you sure you want to activate the selected images?',
    'confirm_bulk_deactivate' => 'Are you sure you want to deactivate the selected images?',

    // Image Editor
    'edit_image' => 'Edit Image',
    'crop_image' => 'Crop Image',
    'resize_image' => 'Resize Image',
    'rotate_image' => 'Rotate Image',
    'flip_image' => 'Flip Image',
    'adjust_brightness' => 'Adjust Brightness',
    'adjust_contrast' => 'Adjust Contrast',
    'apply_filters' => 'Apply Filters',
    'reset_changes' => 'Reset Changes',
    'save_changes' => 'Save Changes',
    'cancel_changes' => 'Cancel Changes',

    // Image Gallery
    'image_gallery' => 'Image Gallery',
    'view_full_size' => 'View Full Size',
    'download_image' => 'Download Image',
    'copy_image_url' => 'Copy Image URL',
    'share_image' => 'Share Image',
    'print_image' => 'Print Image',

    // Search and Filter
    'search_images' => 'Search Images',
    'filter_by_variant' => 'Filter by Variant',
    'filter_by_status' => 'Filter by Status',
    'filter_by_date' => 'Filter by Date',
    'clear_filters' => 'Clear Filters',
    'apply_filters' => 'Apply Filters',

    // Export and Import
    'export_images' => 'Export Images',
    'import_images' => 'Import Images',
    'export_selected' => 'Export Selected',
    'import_from_file' => 'Import from File',
    'import_from_url' => 'Import from URL',
    'import_progress' => 'Import Progress',
    'import_completed' => 'Import completed successfully.',
    'import_failed' => 'Import failed.',

    // Permissions
    'view_variant_images' => 'View Variant Images',
    'create_variant_images' => 'Create Variant Images',
    'edit_variant_images' => 'Edit Variant Images',
    'delete_variant_images' => 'Delete Variant Images',
    'manage_variant_images' => 'Manage Variant Images',

    // Audit Log
    'image_created' => 'Variant image created',
    'image_updated' => 'Variant image updated',
    'image_deleted' => 'Variant image deleted',
    'image_activated' => 'Variant image activated',
    'image_deactivated' => 'Variant image deactivated',
    'image_set_primary' => 'Variant image set as primary',
    'image_duplicated' => 'Variant image duplicated',
    'images_reordered' => 'Variant images reordered',
    'bulk_operation_performed' => 'Bulk operation performed on variant images',
];
