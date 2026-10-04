@extends('layouts.public')

@section('content')
    @include('public.home.hero')
    @include('public.home.trust')

    @if($typeCards->isNotEmpty())
        @include('public.home.types')
    @endif

    @if($featuredSale->isNotEmpty() || $featuredRent->isNotEmpty())
        @include('public.home.featured-properties')
    @endif

    @if($latestSale->isNotEmpty() || $latestRent->isNotEmpty())
        @include('public.home.latest-properties')
    @endif

    @if($areas->isNotEmpty())
        @include('public.home.locations')
    @endif

    @include('public.home.why')

    <section class="uh-section uh-closing">
        @include('public.home.cta')
    </section>
@endsection
