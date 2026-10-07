@extends('layouts.public')

@section('content')
    @include('public.home.hero')

    @if($trending->isNotEmpty())
        @include('public.home.trending-properties')
    @endif

    @if($featured->isNotEmpty())
        @include('public.home.featured-properties')
    @endif

    @if($areas->isNotEmpty())
        @include('public.home.locations')
    @endif

    @if($typeGroups->isNotEmpty())
        @include('public.home.types')
    @endif

    @if($latestSale->isNotEmpty() || $latestRent->isNotEmpty())
        @include('public.home.latest-properties')
    @endif

    @include('public.home.why')

    @if($articles->isNotEmpty())
        @include('public.home.articles')
    @endif

    @if($faqs->isNotEmpty())
        @include('public.home.faq')
    @endif
@endsection
