<x-ui.admin-related label="Property catalogue">
    @can('reference.manage')
        <a href="{{ route('admin.areas.index') }}">Areas</a>
        <a href="{{ route('admin.property-types.index') }}">Property types</a>
        <a href="{{ route('admin.amenities.index') }}">Amenities</a>
    @endcan
    @can('settings.update')
        <a href="{{ route('admin.listing-display') }}">Listing display</a>
    @endcan
    <a href="{{ route('admin.properties.index') }}">Properties</a>
    <a href="{{ route('admin.projects.index') }}">Projects</a>
</x-ui.admin-related>
