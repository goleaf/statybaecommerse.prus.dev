<?php

declare(strict_types=1);

namespace App\OpenApi\V1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'StatyBae Commerce API',
    description: 'Public HTTP API for campaign analytics, autocomplete suggestions, discount helpers, and system settings.'
)]
#[OA\Server(
    url: '/api',
    description: 'Primary API gateway'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Use a Laravel Sanctum bearer token to access authenticated endpoints.'
)]
#[OA\Tag(name: 'Campaign Clicks', description: 'Track, analyse, and export campaign click data.')]
#[OA\Tag(name: 'Autocomplete', description: 'Search suggestions and lookups used across the storefront and admin surfaces.')]
#[OA\Tag(name: 'System Settings', description: 'Public access to read-only system setting data for clients and integrations.')]
#[OA\Tag(name: 'Discount Conditions', description: 'Utilities that expose discount condition metadata and testing helpers.')]
#[OA\Schema(
    schema: 'PaginationMeta',
    description: 'Pagination metadata that accompanies list responses.',
    type: 'object',
    required: ['total', 'count', 'per_page', 'current_page', 'total_pages', 'has_more_pages'],
    properties: [
        new OA\Property(property: 'total', type: 'integer', format: 'int64'),
        new OA\Property(property: 'count', type: 'integer', format: 'int64'),
        new OA\Property(property: 'per_page', type: 'integer', format: 'int64'),
        new OA\Property(property: 'current_page', type: 'integer', format: 'int64'),
        new OA\Property(property: 'total_pages', type: 'integer', format: 'int64'),
        new OA\Property(property: 'has_more_pages', type: 'boolean'),
        new OA\Property(
            property: 'query',
            description: 'Raw query state that produced the current page.',
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'PaginationLinks',
    description: 'Pagination navigation links for a collection.',
    type: 'object',
    properties: [
        new OA\Property(property: 'first', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'last', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'prev', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'next', type: 'string', format: 'uri', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ProblemDetails',
    type: 'object',
    required: ['status', 'title', 'detail'],
    properties: [
        new OA\Property(property: 'type', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'status', type: 'integer', format: 'int32'),
        new OA\Property(property: 'detail', type: 'string'),
        new OA\Property(property: 'instance', type: 'string', nullable: true),
        new OA\Property(property: 'error_code', type: 'string', nullable: true),
        new OA\Property(property: 'trace_id', type: 'string', nullable: true),
        new OA\Property(property: 'locale', type: 'string', nullable: true),
        new OA\Property(property: 'context', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationViolation',
    type: 'object',
    required: ['field', 'messages', 'reason'],
    properties: [
        new OA\Property(property: 'field', type: 'string'),
        new OA\Property(property: 'messages', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'reason', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationProblemDetails',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/ProblemDetails'),
        new OA\Schema(properties: [
            new OA\Property(
                property: 'context',
                properties: [
                    new OA\Property(
                        property: 'violations',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/ValidationViolation')
                    ),
                ],
                type: 'object'
            ),
        ]),
    ]
)]
final class SharedComponents {}
