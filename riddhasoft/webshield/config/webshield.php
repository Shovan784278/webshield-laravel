<?php

return [
    // Go Engine Proxy URL
    'proxy_url' => env('WEBSHIELD_PROXY_URL', 'http://localhost:8080'),
    
    // API Key for Authentication
    'api_key'   => env('WEBSHIELD_API_KEY', ''),
    
    // Zero-Touch Auto Purge List
    'auto_purge' => [
        \App\Models\Article::class => [
            '/blog/{slug}', 
            '/blog', 
            '/admin/blogs', 
            '/'
        ],
        // Category model add kora holo
        \App\Models\Category::class => [
            '/categories',
            '/'
        ],
    ]
];