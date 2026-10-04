<div class="uh-grid-cards is-large">
    @foreach($properties as $property)
        @include('public.partials.property-card', ['property' => $property, 'sizes' => '(min-width: 768px) 50vw, 100vw'])
    @endforeach
</div>
