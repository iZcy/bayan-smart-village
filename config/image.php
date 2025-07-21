<?php

// config/image.php - Configuration for Intervention Image
return [
    'driver' => 'gd', // or 'imagick'

    'quality' => [
        'default' => 85,
        'thumbnail' => 80,
        'web' => 85,
        'print' => 95,
    ],

    'sizes' => [
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
        ],
        'medium' => [
            'width' => 800,
            'height' => 600,
        ],
        'large' => [
            'width' => 1200,
            'height' => 900,
        ],
    ],

    'allowed_types' => [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'webp',
        'svg'
    ],

    'max_file_size' => 10 * 1024 * 1024, // 10MB

    'directories' => [
        'villages' => 'villages',
        'articles' => 'articles',
        'products' => 'products',
        'gallery' => 'gallery',
        'media' => 'media',
        'thumbnails' => 'media/thumbnails',
        'cache' => 'cache',
    ],
];
