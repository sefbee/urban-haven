@php($meta = $seo ?? ['title' => config('app.name'), 'description' => null, 'image' => null, 'noindex' => false])
<meta name="title" content="{{ $meta['title'] }}">
@if(!empty($meta['description']))
    <meta name="description" content="{{ $meta['description'] }}">
@endif
@if(!empty($meta['image']))
    <meta property="og:image" content="{{ $meta['image'] }}">
@endif
@if(!empty($meta['noindex']))
    <meta name="robots" content="noindex">
@endif
<link rel="canonical" href="{{ url()->current() }}">
