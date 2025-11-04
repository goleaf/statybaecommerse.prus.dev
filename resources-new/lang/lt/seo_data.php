<?php

return [
    'title' => 'SEO Data',
    'plural' => 'SEO Data',
    'single' => 'SEO Data',

    'sections' => [
        'basic_info' => 'Basic Information',
        'basic_info_description' => 'Essential details about the SEO data',
        'seo_content' => 'SEO Content',
        'seo_content_description' => 'Title, description, keywords and canonical URL',
        'robots' => 'Robots',
        'robots_description' => 'Search engine indexing and following settings',
        'advanced' => 'Advanced Settings',
        'advanced_description' => 'Meta tags and structured data',
        'seo_analysis' => 'SEO Analysis',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'seoable_type' => 'SEOable Type',
        'seoable_id' => 'SEOable ID',
        'seoable_name' => 'SEOable Name',
        'locale' => 'Locale',
        'title' => 'Title',
        'description' => 'Description',
        'keywords' => 'Keywords',
        'canonical_url' => 'Canonical URL',
        'no_index' => 'No Index',
        'no_follow' => 'No Follow',
        'robots' => 'Robots',
        'meta_tags' => 'Meta Tags',
        'meta_tag_name' => 'Meta Tag Name',
        'meta_tag_content' => 'Meta Tag Content',
        'structured_data' => 'Structured Data',
        'structured_data_key' => 'Structured Data Key',
        'structured_data_value' => 'Structured Data Value',
        'seo_score' => 'SEO Score',
        'title_length' => 'Title Length',
        'description_length' => 'Description Length',
        'keywords_count' => 'Keywords Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'types' => [
        'product' => 'Product',
        'category' => 'Category',
        'brand' => 'Brand',
    ],

    'filters' => [
        'seoable_type' => 'SEOable Type',
        'locale' => 'Locale',
        'no_index' => 'No Index',
        'no_follow' => 'No Follow',
        'has_title' => 'Has Title',
        'has_description' => 'Has Description',
        'has_keywords' => 'Has Keywords',
        'has_canonical_url' => 'Has Canonical URL',
        'high_seo_score' => 'High SEO Score',
    ],

    'actions' => [
        'analyze_seo' => 'Analyze SEO',
        'generate_meta_tags' => 'Generate Meta Tags',
        'analyze_all_seo' => 'Analyze All SEO',
        'generate_all_meta_tags' => 'Generate All Meta Tags',
        'add_meta_tag' => 'Add Meta Tag',
        'add_structured_data' => 'Add Structured Data',
    ],

    'notifications' => [
        'seo_analyzed' => 'SEO analyzed successfully',
        'meta_tags_generated' => 'Meta tags generated successfully',
        'all_seo_analyzed' => 'All SEO analyzed successfully',
        'all_meta_tags_generated' => 'All meta tags generated successfully',
    ],

    'warnings' => [
        'title_too_short' => 'Title is too short (recommended: 30-60 characters)',
        'title_too_long' => 'Title is too long (recommended: 30-60 characters)',
        'description_too_short' => 'Description is too short (recommended: 120-160 characters)',
        'description_too_long' => 'Description is too long (recommended: 120-160 characters)',
    ],

    'placeholders' => [
        'seoable_not_found' => 'SEOable not found',
        'no_description' => 'No description',
        'no_keywords' => 'No keywords',
        'no_canonical_url' => 'No canonical URL',
        'no_meta_tags' => 'No meta tags',
        'no_structured_data' => 'No structured data',
        'keywords_comma_separated' => 'Keywords separated by commas',
    ],

    'characters' => 'characters',
];
