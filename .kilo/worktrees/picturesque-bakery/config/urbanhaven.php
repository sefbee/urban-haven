<?php

return [

    /*
    | Display timezone for staff and public clocks. Database timestamps remain UTC.
    */
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Dhaka'),

    /*
    | Bangladesh land-area conversion table (to square feet).
    | Confirm regional Katha/Bigha values with the client before launch.
    */
    'area_units' => [
        'sqft' => ['label' => 'Square feet', 'to_sqft' => 1],
        'sqm' => ['label' => 'Square metres', 'to_sqft' => 10.76391041671],
        'katha' => ['label' => 'Katha', 'to_sqft' => 720],
        'bigha' => ['label' => 'Bigha', 'to_sqft' => 14400],
        'decimal' => ['label' => 'Decimal', 'to_sqft' => 435.6],
        'acre' => ['label' => 'Acre', 'to_sqft' => 43560],
        'shotok' => ['label' => 'Shotok', 'to_sqft' => 435.6],
    ],

    'currency' => [
        'code' => env('APP_CURRENCY', 'BDT'),
        'label' => env('APP_CURRENCY_LABEL', 'BDT'),
        'locale' => env('APP_CURRENCY_LOCALE', 'en_BD'),
    ],

    'lead' => [
        'repeat_window_days' => (int) env('LEAD_REPEAT_WINDOW_DAYS', 30),
        'retention_days' => (int) env('LEAD_RETENTION_DAYS', 730),
    ],

    'search' => [
        'page_size' => 12,
        'max_page_size' => 24,
        'price_band_percent' => 25,
        'similar_limit' => 6,
        'compare_limit' => 4,
        'recently_viewed_limit' => 10,
    ],

    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER'),
    ],

    'maps' => [
        'tile_url' => env('MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
        'default_lat' => (float) env('MAP_DEFAULT_LAT', 23.8103),
        'default_lng' => (float) env('MAP_DEFAULT_LNG', 90.4125),
        'approximate_decimals' => 2,
    ],

    'media' => [
        'max_image_kb' => 8192,
        'max_brochure_kb' => 20480,
        'derivative_widths' => [480, 768, 1280, 1920],
        'allowed_video_hosts' => ['youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com'],
    ],

    'queue_fallback' => env('QUEUE_FALLBACK_CONNECTION', 'database'),
];
