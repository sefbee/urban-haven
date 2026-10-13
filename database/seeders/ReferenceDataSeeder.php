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

        $amenities = [
            'pool' => ['label' => 'Swimming pool', 'icon_class' => 'fa-light fa-person-swimming'],
            'gym' => ['label' => 'Fitness centre / Gym', 'icon_class' => 'fa-light fa-dumbbell'],
            'parking' => ['label' => 'Dedicated parking', 'icon_class' => 'fa-light fa-square-parking'],
            'security' => ['label' => '24/7 Security & CCTV', 'icon_class' => 'fa-light fa-shield-check'],
            'lift' => ['label' => 'High-speed passenger lift', 'icon_class' => 'fa-light fa-elevator'],
            'generator' => ['label' => 'Full backup generator', 'icon_class' => 'fa-light fa-bolt'],
            'garden' => ['label' => 'Landscaped garden & lawn', 'icon_class' => 'fa-light fa-tree'],
            'rooftop' => ['label' => 'Rooftop terrace lounge', 'icon_class' => 'fa-light fa-building'],
            'intercom' => ['label' => 'Video intercom facility', 'icon_class' => 'fa-light fa-phone-office'],
            'fire_exit' => ['label' => 'Fire safety & emergency exit', 'icon_class' => 'fa-light fa-fire-extinguisher'],
            'gas' => ['label' => 'Central gas connection', 'icon_class' => 'fa-light fa-fire-flame-curved'],
            'play_area' => ['label' => "Children's play area", 'icon_class' => 'fa-light fa-gamepad'],
            'community_hall' => ['label' => 'Multi-purpose community hall', 'icon_class' => 'fa-light fa-users'],
            'servant_room' => ['label' => 'Servant room & washroom', 'icon_class' => 'fa-light fa-door-closed'],
            'solar' => ['label' => 'Rooftop solar energy system', 'icon_class' => 'fa-light fa-sun'],
            'smart_home' => ['label' => 'Smart home automation', 'icon_class' => 'fa-light fa-house-laptop'],
        ];

        foreach ($amenities as $key => $data) {
            Amenity::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $data['label'],
                    'icon_class' => $data['icon_class'],
                    'is_active' => true,
                ]
            );
        }
    }
}
