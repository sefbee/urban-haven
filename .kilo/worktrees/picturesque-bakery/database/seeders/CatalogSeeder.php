<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@urbanhaven.test'],
            [
                'name' => 'Ayesha Rahman',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );
        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@urbanhaven.test'],
            [
                'name' => 'Nafis Chowdhury',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );
        $sales = User::query()->updateOrCreate(
            ['email' => 'sales@urbanhaven.test'],
            [
                'name' => 'Farhan Ahmed',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        $owner->roles()->sync([Role::query()->where('key', Role::OWNER_ADMIN)->first()->id]);
        $editor->roles()->sync([Role::query()->where('key', Role::CONTENT_EDITOR)->first()->id]);
        $sales->roles()->sync([Role::query()->where('key', Role::SALES_USER)->first()->id]);

        Setting::set('company_name', 'Urban Haven Properties Ltd.', 'branding');
        Setting::set('phone', '+8801711000000', 'contact');
        Setting::set('whatsapp', config('urbanhaven.whatsapp.number', '+8801711000000'), 'contact');
        Setting::set('analytics_script', '', 'analytics');
        Setting::set('email', 'hello@urbanhaven.test', 'contact');

        $gulshan = LocationArea::query()->updateOrCreate(['slug' => 'gulshan'], ['name' => 'Gulshan', 'city' => 'Dhaka', 'is_active' => true]);
        $dhanmondi = LocationArea::query()->updateOrCreate(['slug' => 'dhanmondi'], ['name' => 'Dhanmondi', 'city' => 'Dhaka', 'is_active' => true]);
        $banani = LocationArea::query()->updateOrCreate(['slug' => 'banani'], ['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true]);

        $apartment = PropertyType::query()->updateOrCreate(['key' => 'apartment'], ['label' => 'Apartment', 'is_active' => true]);
        $duplex = PropertyType::query()->updateOrCreate(['key' => 'duplex'], ['label' => 'Duplex', 'is_active' => true]);
        $commercial = PropertyType::query()->updateOrCreate(['key' => 'commercial'], ['label' => 'Commercial', 'is_active' => true]);

        $pool = Amenity::query()->updateOrCreate(['key' => 'pool'], ['label' => 'Swimming pool', 'is_active' => true]);
        $gym = Amenity::query()->updateOrCreate(['key' => 'gym'], ['label' => 'Gym', 'is_active' => true]);
        $parking = Amenity::query()->updateOrCreate(['key' => 'parking'], ['label' => 'Parking', 'is_active' => true]);
        $security = Amenity::query()->updateOrCreate(['key' => 'security'], ['label' => '24/7 security', 'is_active' => true]);

        $project = Project::query()->updateOrCreate(['slug' => 'haven-residences-gulshan'], [
            'name' => 'Haven Residences Gulshan',
            'description' => 'A quiet, well-planned residential address in Gulshan with generous daylight, landscaped courtyards and carefully specified interiors.',
            'development_stage' => 'ongoing',
            'city' => 'Dhaka',
            'location_area_id' => $gulshan->id,
            'lat' => 23.7925,
            'lng' => 90.4078,
            'developer_name' => 'Urban Haven Properties Ltd.',
            'completion_date' => now()->addMonths(14)->toDateString(),
            'handover_info' => 'Handover scheduled after finishing works and utility connections.',
            'amenity_ids' => [$pool->id, $gym->id, $parking->id, $security->id],
            'highlights' => ['Landscaped courtyard', 'Two basement parking levels', 'Backup power'],
            'trust_label' => 'RAJUK-approved development',
            'is_featured' => true,
        ]);
        $project->publicationState()->updateOrCreate([], [
            'status' => PublicationState::PUBLISHED,
            'published_by' => $owner->id,
            'published_at' => now(),
        ]);

        $listings = [
            [
                'slug' => 'gulshan-south-three-bed',
                'title' => 'South-facing 3 bed in Gulshan',
                'location_area_id' => $gulshan->id,
                'property_type_id' => $apartment->id,
                'project_id' => $project->id,
                'listing_type' => 'sale',
                'price' => 28500000,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area_value' => 1850,
                'floor_number' => 7,
                'lat' => 23.7928,
                'lng' => 90.4081,
                'is_featured' => true,
                'trust_label' => 'Verified listing',
            ],
            [
                'slug' => 'dhanmondi-lake-two-bed',
                'title' => 'Lake-side 2 bed in Dhanmondi',
                'location_area_id' => $dhanmondi->id,
                'property_type_id' => $apartment->id,
                'listing_type' => 'rent',
                'price' => 85000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_value' => 1250,
                'floor_number' => 4,
                'lat' => 23.7461,
                'lng' => 90.3742,
                'is_featured' => true,
            ],
            [
                'slug' => 'banani-corner-duplex',
                'title' => 'Corner duplex in Banani',
                'location_area_id' => $banani->id,
                'property_type_id' => $duplex->id,
                'listing_type' => 'sale',
                'price' => 42000000,
                'bedrooms' => 4,
                'bathrooms' => 4,
                'area_value' => 2800,
                'floor_number' => 8,
                'lat' => 23.7937,
                'lng' => 90.4043,
            ],
            [
                'slug' => 'gulshan-commercial-floor',
                'title' => 'Fitted commercial floor, Gulshan 2',
                'location_area_id' => $gulshan->id,
                'property_type_id' => $commercial->id,
                'listing_type' => 'rent',
                'price' => 220000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => null,
                'bathrooms' => 2,
                'area_value' => 3200,
                'lat' => 23.7945,
                'lng' => 90.4140,
            ],
        ];

        foreach ($listings as $data) {
            $property = Property::query()->updateOrCreate(['slug' => $data['slug']], [
                'reference' => 'UH-'.strtoupper(str_replace('-', '', $data['slug'])),
                'description' => 'Thoughtfully planned interiors, honest specifications and a location that holds value. Viewings by appointment.',
                'availability' => 'available',
                'price_basis' => $data['price_basis'] ?? 'total_sale',
                'area_unit' => 'sqft',
                'is_furnished' => false,
                'amenity_ids' => [$parking->id, $security->id],
                'trust_label' => $data['trust_label'] ?? 'Company listing',
                'is_featured' => $data['is_featured'] ?? false,
                'project_id' => $data['project_id'] ?? null,
                'property_type_id' => $data['property_type_id'],
                'location_area_id' => $data['location_area_id'],
                'title' => $data['title'],
                'listing_type' => $data['listing_type'],
                'price' => $data['price'],
                'bedrooms' => $data['bedrooms'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'area_value' => $data['area_value'],
                'floor_number' => $data['floor_number'] ?? null,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
            ]);

            $property->publicationState()->updateOrCreate([], [
                'status' => PublicationState::PUBLISHED,
                'published_by' => $owner->id,
                'published_at' => now(),
            ]);
        }

        CmsBlock::query()->updateOrCreate(['key' => 'hero'], [
            'label' => 'Homepage hero',
            'content' => [
                'eyebrow' => 'Urban Haven Properties Ltd.',
                'title' => 'Homes with quiet confidence in Dhaka.',
                'body' => 'Search company-owned apartments, duplexes and project units. Every listing is published by our own team — not a marketplace of unknown sellers.',
                'cta_label' => 'Browse homes',
                'cta_url' => '/properties',
            ],
            'updated_at' => now(),
        ]);

        CmsBlock::query()->updateOrCreate(['key' => 'about'], [
            'label' => 'Homepage about',
            'content' => [
                'title' => 'Built for buyers who want facts, not theatre.',
                'body' => 'We publish what we can stand behind: availability, size, location and a direct line to our sales desk.',
            ],
            'updated_at' => now(),
        ]);

        CmsBlock::query()->updateOrCreate(['key' => 'contact_details'], [
            'label' => 'Contact details',
            'content' => [
                'phone' => '+880 1711 000000',
                'email' => 'hello@urbanhaven.test',
                'address' => 'Gulshan, Dhaka',
            ],
            'updated_at' => now(),
        ]);

        CmsBlock::query()->updateOrCreate(['key' => 'intent_blocks'], [
            'label' => 'Intent entry blocks',
            'content' => [
                'items' => [
                    ['label' => 'Buy', 'url' => '/properties?listing_type=sale'],
                    ['label' => 'Rent', 'url' => '/properties?listing_type=rent'],
                    ['label' => 'Projects', 'url' => '/projects'],
                    ['label' => 'Commercial', 'url' => '/properties?property_type_id='.$commercial->id],
                ],
            ],
            'updated_at' => now(),
        ]);

        foreach ([
            ['slug' => 'about', 'title' => 'About Urban Haven', 'body' => '<p>Urban Haven Properties Ltd. develops and sells its own inventory in Dhaka. This website is the public face of that work.</p>'],
            ['slug' => 'contact', 'title' => 'Contact', 'body' => '<p>Call, WhatsApp or send an enquiry. A member of the sales desk will follow up.</p>'],
            ['slug' => 'services', 'title' => 'Services', 'body' => '<p>Sales, lettings and project handover support for company inventory.</p>'],
        ] as $page) {
            $model = CmsPage::query()->updateOrCreate(['slug' => $page['slug']], [
                'title' => $page['title'],
                'body' => $page['body'],
                'status' => 'published',
                'created_by' => $owner->id,
                'updated_by' => $owner->id,
            ]);
            $model->publicationState()->updateOrCreate([], [
                'status' => PublicationState::PUBLISHED,
                'published_by' => $owner->id,
                'published_at' => now(),
            ]);
        }
    }
}
