@extends('layouts.public')

@section('content')
    @include('public.home.hero')
    @include('public.home.trust')

    @if($projects->isNotEmpty())
        @include('public.home.projects')
    @endif

    @if($featuredSale->isNotEmpty() || $featuredRent->isNotEmpty())
        @include('public.home.featured-properties')
    @endif

    @if($latestSale->isNotEmpty() || $latestRent->isNotEmpty())
        @include('public.home.latest-properties')
    @endif

    @if($typeCards->isNotEmpty())
        @include('public.home.types')
    @endif

    @if($areas->isNotEmpty())
        @include('public.home.locations')
    @endif

    @include('public.home.why')
    @include('public.home.cta')
    @include('public.home.discover')
@endsection
