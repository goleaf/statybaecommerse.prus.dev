<?php

return [
    // Basic fields
    'title' => 'Straipsniai',
    'post' => 'Straipsnis',
    'fields' => [
        'title' => 'Pavadinimas',
        'slug' => 'Slug',
        'content' => 'Turinys',
        'excerpt' => 'Santrauka',
        'status' => 'Būsena',
        'published_at' => 'Publikuota',
        'featured' => 'Išskirtinis',
        'is_pinned' => 'Prisegtas',
        'user_id' => 'Autorius',
        'meta_title' => 'Meta pavadinimas',
        'meta_description' => 'Meta aprašymas',
        'tags' => 'Žymos',
        'images' => 'Pagrindinis vaizdas',
        'gallery' => 'Galerija',
        'views_count' => 'Peržiūros',
        'likes_count' => 'Patinka',
        'comments_count' => 'Komentarai',
        'allow_comments' => 'Leisti komentarus',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Status
    'status' => [
        'draft' => 'Juodraštis',
        'published' => 'Publikuotas',
        'archived' => 'Archyvuotas',
    ],

    // Actions
    'actions' => [
        'publish' => 'Publikuoti',
        'unpublish' => 'Nepublikuoti',
        'archive' => 'Archyvuoti',
        'feature' => 'Išskirti',
        'unfeature' => 'Neišskirti',
        'pin' => 'Prisegti',
        'unpin' => 'Atsegti',
    ],

    // Filters
    'filters' => [
        'status' => 'Būsena',
        'featured' => 'Išskirtinis',
        'all_posts' => 'Visi straipsniai',
        'featured_only' => 'Tik išskirtiniai',
        'not_featured' => 'Neišskirtiniai',
        'author' => 'Autorius',
        'published_from' => 'Publikuota nuo',
        'published_until' => 'Publikuota iki',
    ],

    // SEO
    'seo' => [
        'meta_title_help' => 'Rekomenduojamas ilgis: 50-60 simbolių',
        'meta_description_help' => 'Rekomenduojamas ilgis: 150-160 simbolių',
    ],

    // Widgets
    'widgets' => [
        'total_posts' => 'Iš viso straipsnių',
        'published_posts' => 'Publikuoti straipsniai',
        'draft_posts' => 'Juodraščiai',
        'featured_posts' => 'Išskirtiniai straipsniai',
        'posts_by_status' => 'Straipsniai pagal būseną',
        'recent_posts' => 'Naujausi straipsniai',
    ],

    // Engagement
    'engagement' => [
        'total_views' => 'Iš viso peržiūrų',
        'total_views_description' => 'Visų laikų puslapių peržiūros',
        'total_likes' => 'Iš viso patinka',
        'total_likes_description' => 'Visų laikų gauti patinka',
        'total_comments' => 'Iš viso komentarų',
        'total_comments_description' => 'Visų laikų gauti komentarai',
        'total_engagement' => 'Iš viso sąveikos',
        'total_engagement_description' => 'Kombinuoti patinka ir komentarai',
        'average_views' => 'Vidutinis peržiūrų skaičius',
        'average_views_description' => 'Vidutinis peržiūrų skaičius per straipsnį',
        'average_likes' => 'Vidutinis patinka skaičius',
        'average_likes_description' => 'Vidutinis patinka skaičius per straipsnį',
        'average_comments' => 'Vidutinis komentarų skaičius',
        'average_comments_description' => 'Vidutinis komentarų skaičius per straipsnį',
        'average_engagement_rate' => 'Vidutinis sąveikos procentas',
        'average_engagement_rate_description' => 'Vidutinis sąveikos procentas',
    ],

    // Performance
    'performance' => [
        'most_viewed' => 'Daugiausiai peržiūrų',
        'most_liked' => 'Daugiausiai patinka',
        'most_commented' => 'Daugiausiai komentarų',
        'most_popular' => 'Populiariausias',
        'no_posts' => 'Straipsnių nėra',
    ],

    // Authors
    'authors' => [
        'posts_count' => 'Straipsnių skaičius',
        'posts' => 'Straipsniai',
    ],

    // Media
    'media' => [
        'posts_with_media' => 'Straipsniai su medija',
        'posts_with_media_description' => 'Straipsniai, turintys medijos failus',
        'posts_without_media' => 'Straipsniai be medijos',
        'posts_without_media_description' => 'Straipsniai be medijos failų',
        'posts_with_featured_image' => 'Straipsniai su pagrindiniu vaizdu',
        'posts_with_featured_image_description' => 'Straipsniai su pagrindiniais vaizdais',
        'posts_with_gallery' => 'Straipsniai su galerija',
        'posts_with_gallery_description' => 'Straipsniai su vaizdų galerijomis',
        'total_media_files' => 'Iš viso medijos failų',
        'total_media_files_description' => 'Iš viso medijos failų visuose straipsniuose',
        'average_media_per_post' => 'Vidutinis medijos skaičius per straipsnį',
        'average_media_per_post_description' => 'Vidutinis medijos failų skaičius per straipsnį',
    ],

    // Empty states
    'empty_states' => [
        'no_posts' => 'Straipsnių nerasta',
        'no_published_posts' => 'Nėra publikuotų straipsnių',
        'no_draft_posts' => 'Nėra juodraščių',
        'no_featured_posts' => 'Nėra išskirtinių straipsnių',
    ],

    // Messages
    'messages' => [
        'created' => 'Straipsnis sėkmingai sukurtas',
        'updated' => 'Straipsnis sėkmingai atnaujintas',
        'deleted' => 'Straipsnis sėkmingai ištrintas',
        'published' => 'Straipsnis sėkmingai publikuotas',
        'unpublished' => 'Straipsnis sėkmingai nepublikuotas',
        'archived' => 'Straipsnis sėkmingai archyvuotas',
        'featured' => 'Straipsnis sėkmingai išskirtas',
        'unfeatured' => 'Straipsnis sėkmingai neišskirtas',
    ],

    // Validation
    'validation' => [
        'title_required' => 'Pavadinimas yra privalomas',
        'title_max' => 'Pavadinimas negali viršyti 255 simbolių',
        'slug_required' => 'Slug yra privalomas',
        'slug_unique' => 'Slug turi būti unikalus',
        'slug_alpha_dash' => 'Slug gali turėti tik raides, skaičius, brūkšnelius ir pabraukimus',
        'content_required' => 'Turinys yra privalomas',
        'status_required' => 'Būsena yra privaloma',
        'user_required' => 'Autorius yra privalomas',
        'published_at_required' => 'Publikuojimo data yra privaloma',
        'meta_title_max' => 'Meta pavadinimas negali viršyti 60 simbolių',
        'meta_description_max' => 'Meta aprašymas negali viršyti 160 simbolių',
    ],
];
