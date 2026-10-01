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
            ['key' => 'plot', 'label' => 'Plot', 'category' => 'land', 'field_profile' => 'plot'],
            ['key' => 'commercial', 'label' => 'Commercial', 'category' => 'commercial', 'field_profile' => 'commercial'],
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
