@extends('layouts.admin')
@section('title', 'Listing display')

@section('content')
    <x-ui.page-header compact title="Listing display"
                      description="What the public site may show for sale or rent, and how precise a listing’s map pin is.">
        <x-slot:eyebrow>Catalogue</x-slot:eyebrow>
    </x-ui.page-header>

    @include('admin.catalogue._related')

    <form method="POST" action="{{ route('admin.settings.update') }}" class="uh-admin-compose-main" x-data="uhForm" @submit="submit">
        @csrf
        @method('PUT')
        <div class="uh-admin-stack">
            @include('admin.settings._fields', ['groups' => ['listings']])
        </div>

        <div class="uh-admin-dock">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span>Save listing display</span>
            </button>
        </div>
    </form>
@endsection
