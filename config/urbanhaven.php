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
        'duplicate_window_seconds' => (int) env('LEAD_DUPLICATE_WINDOW_SECONDS', 60),
        'retention_days' => (int) env('LEAD_RETENTION_DAYS', 730),
        'per_phone_per_hour' => (int) env('LEAD_PER_PHONE_PER_HOUR', 5),
        'visit_window_days' => 90,
        'export_queue_threshold' => (int) env('LEAD_EXPORT_QUEUE_THRESHOLD', 2000),
    ],

    'inventory' => [
        'reference_prefix' => env('PROPERTY_REFERENCE_PREFIX', 'UH'),
        'reservation_days' => (int) env('RESERVATION_DAYS', 14),
    ],

    'mfa' => [
        'enforce' => (bool) env('MFA_ENFORCED', false),
        'required_roles' => ['owner_admin'],
        'issuer' => env('MFA_ISSUER', env('APP_NAME', 'Urban Haven')),
    ],

    'analytics' => [
        'gtm_id' => env('ANALYTICS_GTM_ID'),
        'ga4_id' => env('ANALYTICS_GA4_ID'),
        'meta_pixel_id' => env('ANALYTICS_META_PIXEL_ID'),
        'consent_cookie' => 'uh_consent',
    ],

    'seo' => [
        'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),
        'indexable_query_keys' => ['listing_type', 'page'],
    ],

    'backups' => [
        'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    ],

    'facings' => [
        'north' => 'North',
        'south' => 'South',
        'east' => 'East',
        'west' => 'West',
        'north-east' => 'North-East',
        'north-west' => 'North-West',
        'south-east' => 'South-East',
        'south-west' => 'South-West',
    ],

    'price_bands' => [
        'sale' => [
            'label' => 'Buy',
            'options' => [
                'sale-under-50l' => ['label' => 'Under BDT 50 lakh', 'min' => null, 'max' => 5_000_000],
                'sale-50l-1cr' => ['label' => 'BDT 50 lakh – 1 crore', 'min' => 5_000_000, 'max' => 10_000_000],
                'sale-1-2cr' => ['label' => 'BDT 1 – 2 crore', 'min' => 10_000_000, 'max' => 20_000_000],
                'sale-2-5cr' => ['label' => 'BDT 2 – 5 crore', 'min' => 20_000_000, 'max' => 50_000_000],
                'sale-5cr-plus' => ['label' => 'BDT 5 crore+', 'min' => 50_000_000, 'max' => null],
            ],
        ],
        'rent' => [
            'label' => 'Rent',
            'options' => [
                'rent-under-20k' => ['label' => 'Under BDT 20,000', 'min' => null, 'max' => 20_000],
                'rent-20-40k' => ['label' => 'BDT 20,000 – 40,000', 'min' => 20_000, 'max' => 40_000],
                'rent-40-80k' => ['label' => 'BDT 40,000 – 80,000', 'min' => 40_000, 'max' => 80_000],
                'rent-80k-150k' => ['label' => 'BDT 80,000 – 1.5 lakh', 'min' => 80_000, 'max' => 150_000],
                'rent-150k-plus' => ['label' => 'BDT 1.5 lakh+', 'min' => 150_000, 'max' => null],
            ],
        ],
    ],

    'area_bands' => [
        'under-1000' => ['label' => 'Under 1,000 sq ft', 'min' => null, 'max' => 1000],
        '1000-1500' => ['label' => '1,000 – 1,500 sq ft', 'min' => 1000, 'max' => 1500],
        '1500-2500' => ['label' => '1,500 – 2,500 sq ft', 'min' => 1500, 'max' => 2500],
        '2500-5000' => ['label' => '2,500 – 5,000 sq ft', 'min' => 2500, 'max' => 5000],
        '5000-plus' => ['label' => '5,000+ sq ft', 'min' => 5000, 'max' => null],
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
        'tile_url' => env('MAP_TILE_URL', 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'),
        'attribution' => env('MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors &copy; CARTO'),
        'default_lat' => (float) env('MAP_DEFAULT_LAT', 23.8103),
        'default_lng' => (float) env('MAP_DEFAULT_LNG', 90.4125),
        'approximate_decimals' => 2,
        'cluster_threshold' => (int) env('MAP_CLUSTER_THRESHOLD', 60),
        'max_points' => (int) env('MAP_MAX_POINTS', 500),
        'bounds' => ['south' => 20.5, 'north' => 26.7, 'west' => 88.0, 'east' => 92.7],
    ],

    'media' => [
        'max_image_kb' => 8192,
        'max_brochure_kb' => 20480,
        'derivative_widths' => [480, 768, 1280, 1920],
        'allowed_video_hosts' => ['youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com'],
    ],

    'queue_fallback' => env('QUEUE_FALLBACK_CONNECTION', 'database'),
];
