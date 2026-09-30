@extends('layouts.admin')
@section('title', 'Add unit')

@section('content')
    <x-ui.page-header compact title="Add a unit"
                      :description="'Units let one listing cover several apartments with their own prices and availability.'">
        <x-slot:eyebrow>{{ $property->title }}</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.properties.edit', $property) }}">
                <x-icon name="chevron-left" class="size-4" />
                Back to listing
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('admin.units.store', $property) }}" class="uh-panel mt-6 max-w-xl space-y-4"
          x-data="uhForm" @submit="submit">
        @csrf
        <x-ui.input name="unit_number" label="Unit number" required placeholder="4B" />
        <x-ui.input name="price" label="Price" type="number" step="0.01" min="0" inputmode="decimal"
                    hint="In BDT. Leave blank to use the listing price." />
        <x-ui.select name="status" label="Status" required>
            @foreach(['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold', 'rented' => 'Rented'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', 'available') === $value)>{{ $label }}</option>
            @endforeach
        </x-ui.select>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Saving…' : 'Add unit'">Add unit</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.properties.edit', $property) }}">Cancel</a>
        </div>
    </form>
@endsection
