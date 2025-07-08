<?php

// config/smartvillage.php

return [
    /*
    |--------------------------------------------------------------------------
    | Smart Village Environment Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration values control environment-specific behavior
    | for the Smart Village application, particularly for URLs and links.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | URL Configuration
    |--------------------------------------------------------------------------
    |
    | Configure URL behavior based on environment
    |
    */
    'url' => [
        'protocol' => env('APP_ENV') === 'local' ? 'http' : 'https',
        'force_https' => env('FORCE_HTTPS', env('APP_ENV') !== 'local'),
        'local_port' => env('LOCAL_PORT', '8000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Short Link Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the short link generation system
    |
    */
    'short_links' => [
        'default_protocol' => env('APP_ENV') === 'local' ? 'http' : 'https',
        'qr_code_size' => env('QR_CODE_SIZE', 200),
        'local_testing' => env('APP_ENV') === 'local',

        // Test URLs for different environments
        'test_urls' => [
            'local' => [
                'http://localhost:8000',
                'http://127.0.0.1:8000',
                'http://localhost:3000',
                'http://httpbin.org/get',
            ],
            'production' => [
                'https://www.instagram.com/indonesia.travel',
                'https://www.facebook.com/wonderfulindonesia',
                'https://www.youtube.com/channel/UCvVNPfEqQr3lVKo-TQSAnRQ',
                'https://maps.google.com',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Settings specific to development environment
    |
    */
    'development' => [
        'show_env_badges' => env('SHOW_ENV_BADGES', env('APP_ENV') === 'local'),
        'debug_links' => env('DEBUG_LINKS', env('APP_ENV') === 'local'),
        'mock_external_services' => env('MOCK_EXTERNAL_SERVICES', env('APP_ENV') === 'local'),
        'local_domains' => [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            'laravel.test',
            'smartvillage.test',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Services
    |--------------------------------------------------------------------------
    |
    | Configuration for external services based on environment
    |
    */
    'services' => [
        'qr_code' => [
            'api_url' => 'https://api.qrserver.com/v1/create-qr-code/',
            'default_size' => '200x200',
            'format' => 'png',
            'error_correction' => 'M',
            'margin' => 10,
        ],

        'social_media' => [
            'instagram' => [
                'base_url' => 'https://instagram.com/',
                'local_mock' => env('APP_ENV') === 'local' ? 'http://localhost:8000/mock/instagram' : null,
            ],
            'whatsapp' => [
                'base_url' => 'https://wa.me/',
                'local_mock' => env('APP_ENV') === 'local' ? 'http://localhost:8000/mock/whatsapp' : null,
            ],
            'facebook' => [
                'base_url' => 'https://facebook.com/',
                'local_mock' => env('APP_ENV') === 'local' ? 'http://localhost:8000/mock/facebook' : null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeding Configuration
    |--------------------------------------------------------------------------
    |
    | Control how data is seeded based on environment
    |
    */
    'seeding' => [
        'create_test_links' => env('CREATE_TEST_LINKS', env('APP_ENV') === 'local'),
        'use_real_domains' => env('USE_REAL_DOMAINS', env('APP_ENV') !== 'local'),
        'link_count' => [
            'villages' => env('SEED_VILLAGE_LINKS', 4),
            'apex' => env('SEED_APEX_LINKS', 3),
            'places' => env('SEED_PLACE_LINKS', 5),
            'random' => env('SEED_RANDOM_LINKS', 8),
        ],
    ],

    /*
|--------------------------------------------------------------------------
| Products Configuration
|--------------------------------------------------------------------------
|
| Configuration for the products feature
|
*/
    'products' => [
        'per_page' => env('PRODUCTS_PER_PAGE', 12),
        'max_images_per_product' => env('PRODUCTS_MAX_IMAGES', 10),
        'max_ecommerce_links' => env('PRODUCTS_MAX_ECOMMERCE_LINKS', 10),
        'featured_limit' => env('PRODUCTS_FEATURED_LIMIT', 8),

        'supported_platforms' => [
            'tokopedia' => [
                'name' => 'Tokopedia',
                'icon' => 'tokopedia',
                'color' => 'success',
                'url_pattern' => 'tokopedia.com',
            ],
            'shopee' => [
                'name' => 'Shopee',
                'icon' => 'shopee',
                'color' => 'warning',
                'url_pattern' => 'shopee.co.id',
            ],
            'tiktok_shop' => [
                'name' => 'TikTok Shop',
                'icon' => 'tiktok',
                'color' => 'danger',
                'url_pattern' => 'shop.tiktok.com',
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'icon' => 'whatsapp',
                'color' => 'success',
                'url_pattern' => 'wa.me',
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => 'instagram',
                'color' => 'secondary',
                'url_pattern' => 'instagram.com',
            ],
        ],

        'image_settings' => [
            'max_size_mb' => 2,
            'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
            'thumbnail_size' => [300, 300],
            'medium_size' => [600, 600],
            'large_size' => [1200, 1200],
        ],
    ],
];
