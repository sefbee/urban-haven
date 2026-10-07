<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Taxonomy the admin forms need before any listing exists. Safe to run in every environment.
     */
    public function run(): void
    {
        foreach ([
            ['key' => 'apartment', 'label' => 'Apartment', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'duplex', 'label' => 'Duplex', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'house', 'label' => 'House', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'penthouse', 'label' => 'Penthouse', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'residential-building', 'label' => 'Full building', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'plot', 'label' => 'Plot', 'category' => 'residential', 'field_profile' => 'plot'],
            ['key' => 'single-room', 'label' => 'Single room', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'sublet-room', 'label' => 'Sublet room', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'hostel', 'label' => 'Hostel', 'category' => 'residential', 'field_profile' => 'apartment'],
            ['key' => 'commercial', 'label' => 'Commercial', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'office', 'label' => 'Office', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'shop', 'label' => 'Shop', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'showroom', 'label' => 'Showroom', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'restaurant', 'label' => 'Restaurant', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'co-working', 'label' => 'Co-working space', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'warehouse', 'label' => 'Warehouse', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'factory', 'label' => 'Factory', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'hotel', 'label' => 'Hotel', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'commercial-building', 'label' => 'Commercial building', 'category' => 'commercial', 'field_profile' => 'commercial'],
            ['key' => 'commercial-plot', 'label' => 'Commercial plot', 'category' => 'commercial', 'field_profile' => 'plot'],
        ] as $type) {
            PropertyType::query()->firstOrCreate(['key' => $type['key']], [...$type, 'is_active' => true]);
        }

        foreach ([
            'pool' => 'Swimming pool',
            'gym' => 'Gym',
            'parking' => 'Parking',
            'security' => '24/7 security',
            'lift' => 'Lift',
            'generator' => 'Backup generator',
        ] as $key => $label) {
            Amenity::query()->firstOrCreate(['key' => $key], ['label' => $label, 'is_active' => true]);
        }
    }
}
