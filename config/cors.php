<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // ❌ Không nên: allow all
    'allowed_origins' => ['*'],

    // ✅ Nên làm thay thế:
    // 'allowed_origins' => [
    //     'https://your-frontend.com',
    //     'https://admin.your-app.com',
    // ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⚠️ Lưu ý: supports_credentials phải là FALSE nếu dùng '*'
    // Trình duyệt sẽ block nếu bạn set true + '*' cùng nhau
    'supports_credentials' => false,
];
