@extends('layouts.public')

@section('content')
    @php
        $homepageSectionOrder = array_keys($homepageSections);

        if (in_array('why', $homepageSectionOrder, true) && in_array('articles', $homepageSectionOrder, true)) {
            $articlesContent = $homepageSections['articles'];
            unset($homepageSections['articles']);
            $whyIndex = array_search('why', array_keys($homepageSections), true);
            $homepageSections = array_slice($homepageSections, 0, $whyIndex + 1, true)
                + ['articles' => $articlesContent]
                + array_slice($homepageSections, $whyIndex + 1, null, true);
        }
    @endphp

    @foreach($homepageSections as $section => $sectionContent)
        @switch($section)
            @case('hero')
                @include('public.home.hero')
                @break
            @case('housing')
                @if($housingListings->isNotEmpty())
                    @include('public.home.housing-projects')
                @endif
                @break
            @case('projects')
                @if($projects->isNotEmpty())
                    @include('public.home.projects')
                @endif
                @break
            @case('featured')
                @if($featured->isNotEmpty())
                    @include('public.home.featured-properties')
                @endif
                @break
            @case('trending')
                @if($trending->isNotEmpty())
                    @include('public.home.trending-properties')
                @endif
                @break
            @case('locations')
                @if($areas->isNotEmpty())
                    @include('public.home.locations')
                @endif
                @break
            @case('types')
                @if($typeGroups->isNotEmpty())
                    @include('public.home.types')
                @endif
                @break
            @case('latest')
                @if($latestSale->isNotEmpty() || $latestRent->isNotEmpty())
                    @include('public.home.latest-properties')
                @endif
                @break
            @case('requirements')
                @include('public.home.requirements-cta')
                @break
            @case('articles')
                @if($articles->isNotEmpty() || $propertyVideos->isNotEmpty())
                    @include('public.home.articles')
                @endif
                @break
            @case('faq')
                @if($faqs->isNotEmpty())
                    @include('public.home.faq')
                @endif
                @break
            @case('why')
                @include('public.home.why')
                @break
        @endswitch
    @endforeach
@endsection
