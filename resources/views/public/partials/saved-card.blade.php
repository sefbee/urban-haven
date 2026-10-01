<div class="uh-discover-grid">
    @foreach($properties as $property)
        @include('public.partials.property-card', ['property' => $property])
    @endforeach
</div>
