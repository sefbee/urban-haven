@php
    $meta = array_merge(
        ['title' => config('app.name'), 'description' => null, 'image' => null, 'noindex' => false, 'canonical' => \App\Support\SeoMeta::canonical(), 'type' => 'website', 'json_ld' => []],
        $seo ?? [],
    );
    $siteName = \App\Models\Setting::get('company_name', config('app.name'));
    $verification = config('urbanhaven.seo.google_site_verification');
@endphp
@if(!empty($meta['description']))
    <meta name="description" content="{{ $meta['description'] }}">
@endif
@if(!empty($meta['noindex']))
    <meta name="robots" content="noindex, follow">
@else
    <link rel="canonical" href="{{ $meta['canonical'] }}">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $meta['type'] }}">
<meta property="og:title" content="{{ $meta['title'] }}">
@if(!empty($meta['description']))
    <meta property="og:description" content="{{ $meta['description'] }}">
@endif
<meta property="og:url" content="{{ $meta['canonical'] }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
@if(!empty($meta['image']))
    <meta property="og:image" content="{{ $meta['image'] }}">
@endif
<meta name="twitter:card" content="{{ !empty($meta['image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $meta['title'] }}">
@if(!empty($meta['description']))
    <meta name="twitter:description" content="{{ $meta['description'] }}">
@endif
@if(filled($verification))
    <meta name="google-site-verification" content="{{ $verification }}">
@endif
<script type="application/ld+json">{!! \App\Support\StructuredData::encode(\App\Support\StructuredData::organization()) !!}</script>
@foreach($meta['json_ld'] as $schema)
    <script type="application/ld+json">{!! \App\Support\StructuredData::encode($schema) !!}</script>
@endforeach
