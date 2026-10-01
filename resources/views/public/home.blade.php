@extends('layouts.public')

@section('content')
    @include('public.home.hero')
    @include('public.home.valuation-banner')

    @if($projects->isNotEmpty())
        @include('public.home.rail', [
            'eyebrow' => __('Developments'),
            'title' => __('Featured Urban Haven projects'),
            'actionUrl' => route('projects.index'),
            'actionLabel' => __('All projects'),
            'kind' => 'project',
            'items' => $projects,
        ])
    @endif

    @if($saleHomes->isNotEmpty())
        @include('public.home.rail', [
            'eyebrow' => __('Buy'),
            'title' => $saleHeading,
            'actionUrl' => route('properties.index', ['listing_type' => 'sale']),
            'actionLabel' => __('All listings'),
            'kind' => 'property',
            'items' => $saleHomes,
        ])
    @endif

    @if($rentHomes->isNotEmpty())
        @include('public.home.rail', [
            'eyebrow' => __('Rent'),
            'title' => __('Exclusive rental properties'),
            'actionUrl' => route('properties.index', ['listing_type' => 'rent']),
            'actionLabel' => __('All listings'),
            'kind' => 'property',
            'items' => $rentHomes,
        ])
    @endif

    @if($areas->isNotEmpty())
        @include('public.home.locations')
    @endif

    @include('public.home.trust')
@endsection
