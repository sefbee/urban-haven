<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\LocationArea;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SiteVisitRequest;
use App\Models\Unit;
use App\Models\User;
use App\Support\TaggedCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Complete full-application feature content seeder for live production and local environments.
     * Idempotent and safe to run multiple times.
     */
    public function run(): void
    {
        $owner = $this->seedUsers();
        $this->seedSettings();
        $locations = $this->seedLocationAreas();
        $mediaPool = $this->gatherMediaPool();
        $projects = $this->seedProjects($owner, $locations, $mediaPool);
        $this->seedProperties($owner, $locations, $projects, $mediaPool);
        $this->seedArticles($owner, $mediaPool);
        $this->seedCmsBlocksAndPages($owner);
        $this->seedMenus();

        if (app()->environment(['local', 'testing']) || (bool) env('SEED_DEMO_LEADS', false)) {
            $this->seedDemoLeads($owner);
        }

        TaggedCache::flush(['homepage', 'properties', 'projects', 'search', 'sitemap', 'cms', 'menus', 'settings', 'aboutpage', 'contactpage', 'articlespage']);
    }

    /**
     * Seed or locate core staff users.
     */
    protected function seedUsers(): User
    {
        $ownerRole = Role::query()->where('key', Role::OWNER_ADMIN)->first();
        $editorRole = Role::query()->where('key', Role::CONTENT_EDITOR)->first();
        $salesRole = Role::query()->where('key', Role::SALES_USER)->first();

        // In production / live, check if an owner_admin already exists
        $existingOwner = User::query()->whereHas('roles', fn ($q) => $q->where('key', Role::OWNER_ADMIN))->first();

        if ($existingOwner && ! app()->environment(['local', 'testing'])) {
            return $existingOwner;
        }

        $ownerEmail = app()->environment(['local', 'testing'])
            ? 'owner@urbanhaven.test'
            : (env('INITIAL_ADMIN_EMAIL') ?: 'owner@urbanhaven.com');

        $ownerPassword = app()->environment(['local', 'testing'])
            ? 'password'
            : (env('INITIAL_ADMIN_PASSWORD') ?: 'UrbanHaven2026!');

        $owner = User::query()->firstOrCreate(
            ['email' => $ownerEmail],
            [
                'name' => 'Ayesha Rahman',
                'password' => Hash::make($ownerPassword),
                'is_active' => true,
            ]
        );

        if ($ownerRole && ! $owner->roles()->where('role_id', $ownerRole->id)->exists()) {
            $owner->roles()->attach($ownerRole->id);
        }

        // Setup local or testing helper staff accounts
        if (app()->environment(['local', 'testing'])) {
            $editor = User::query()->firstOrCreate(
                ['email' => 'editor@urbanhaven.test'],
                [
                    'name' => 'Nafis Chowdhury',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if ($editorRole && ! $editor->roles()->where('role_id', $editorRole->id)->exists()) {
                $editor->roles()->attach($editorRole->id);
            }

            $sales = User::query()->firstOrCreate(
                ['email' => 'sales@urbanhaven.test'],
                [
                    'name' => 'Farhan Ahmed',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if ($salesRole && ! $sales->roles()->where('role_id', $salesRole->id)->exists()) {
                $sales->roles()->attach($salesRole->id);
            }

            User::query()->firstOrCreate(
                ['email' => 'client@urbanhaven.test'],
                [
                    'name' => 'Tariq Al-Amin',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }

        return $owner;
    }

    /**
     * Seed core application branding and configuration settings.
     */
    protected function seedSettings(): void
    {
        $settings = [
            'company_name' => ['Urban Haven Properties Ltd.', 'branding'],
            'company_tagline' => ['Considered places to live in Dhaka.', 'branding'],
            'phone' => ['+8801711000000', 'contact'],
            'whatsapp' => ['+8801711000000', 'contact'],
            'email' => [app()->environment(['local', 'testing']) ? 'hello@urbanhaven.test' : 'hello@urbanhaven.com', 'contact'],
            'address' => ['House 42, Road 11, Block D, Banani, Dhaka 1213', 'contact'],
            'office_hours' => ['Saturday – Thursday: 9:30 AM – 7:30 PM', 'contact'],
            'map_url' => ['https://maps.google.com/?q=Banani+Dhaka', 'contact'],
            'footer_note' => ['Urban Haven Properties Ltd. is Dhaka’s trusted real estate firm, specializing in premium residential and commercial developments with honest specifications and transparent guidance.', 'chrome'],
            'footer_copyright' => ['All rights reserved. Registered real estate developer in Bangladesh.', 'chrome'],
            'floating_chat_enabled' => [true, 'floating'],
            'floating_chat_desktop' => [true, 'floating'],
            'floating_chat_mobile' => [true, 'floating'],
            'floating_chat_position' => ['bottom-right', 'floating'],
            'floating_chat_message' => ['Hello Urban Haven team, I am interested in exploring properties in Dhaka.', 'floating'],
            'theme_surface' => ['#f8f7f4', 'theme'],
            'theme_dark' => ['#0d1110', 'theme'],
            'theme_primary' => ['#0f766e', 'theme'],
            'enable_sale' => [true, 'listings'],
            'enable_rent' => [true, 'listings'],
            'address_display_mode' => ['approximate', 'listings'],
            'coordinate_precision' => [2, 'listings'],
            'seo_default_title' => ['Urban Haven | Considered Properties & Developments in Dhaka', 'seo'],
            'seo_default_description' => ['Explore premium residential apartments, duplexes, penthouses, and commercial spaces across Gulshan, Banani, Dhanmondi, and Baridhara in Dhaka.', 'seo'],
            'seo_allow_indexing' => [true, 'seo'],
            'consent_text' => ['I agree that Urban Haven may contact me about this enquiry by phone, WhatsApp or email.', 'leads'],
            'social_links' => [[
                ['platform' => 'facebook', 'url' => 'https://facebook.com/urbanhavenbd'],
                ['platform' => 'instagram', 'url' => 'https://instagram.com/urbanhavenbd'],
                ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/urbanhavenbd'],
                ['platform' => 'youtube', 'url' => 'https://youtube.com/@urbanhavenbd'],
            ], 'social'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            Setting::set($key, $value, $group);
        }
    }

    /**
     * Seed primary Dhaka neighbourhoods with intros, coordinates, and local images.
     *
     * @return array<string, LocationArea>
     */
    protected function seedLocationAreas(): array
    {
        $areas = [
            'gulshan' => [
                'name' => 'Gulshan',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.7925,
                'lng' => 90.4078,
                'intro' => 'Gulshan stands as Dhaka’s premier diplomatic and cosmopolitan neighbourhood. Characterized by serene lakeside avenues, high-end dining, diplomatic embassies, and luxury high-rise residences, it offers an exceptional blend of tranquility, prestige, and accessibility.',
                'meta_description' => 'Explore luxury apartments, duplexes, and commercial properties for sale and rent in Gulshan, Dhaka.',
                'image_path' => 'locations/pinZVebRhyobCW1lRJYLy7YalnO4S6zY8cWGNz4M.jpg',
            ],
            'banani' => [
                'name' => 'Banani',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.7937,
                'lng' => 90.4043,
                'intro' => 'A vibrant, leafy neighbourhood where upscale residential streets converge with Dhaka’s most dynamic lifestyle strip. Banani is celebrated for Road 11 boutiques, artisanal cafes, international schools, and boutique residential developments.',
                'meta_description' => 'Find premium apartments, penthouses, and retail showroom spaces in Banani, Dhaka.',
                'image_path' => 'locations/yp68YLdFlOTGLbVr7FiFOQl0rMC77HiAv0v1rTcS.jpg',
            ],
            'dhanmondi' => [
                'name' => 'Dhanmondi',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.7461,
                'lng' => 90.3742,
                'intro' => 'The cultural and intellectual heart of Dhaka. Centred around the scenic Dhanmondi Lake and shaded walkways, this historic residential haven provides effortless access to premier schools, tertiary hospitals, art galleries, and cultural centres.',
                'meta_description' => 'Discover spacious family apartments and lakeside homes in Dhanmondi, Dhaka.',
                'image_path' => 'locations/7s4hFwhjo0ZlrF823ZhrbyYgHOeo9y63Vu68r7IX.jpg',
            ],
            'baridhara' => [
                'name' => 'Baridhara Diplomatic Zone',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.7998,
                'lng' => 90.4206,
                'intro' => 'Dhaka’s most exclusive and secure residential enclave. With perimeter checkpoints, private lakefront promenades, manicured public parks, and unmatched privacy, Baridhara is home to foreign diplomats, heads of mission, and discerning families.',
                'meta_description' => 'Exclusive luxury penthouses and diplomatic residences for sale and rent in Baridhara, Dhaka.',
                'image_path' => 'locations/8Q6te265IJxEri8PqYTEw1fdozcrJYZcxdgFT4y1.jpg',
            ],
            'bashundhara' => [
                'name' => 'Bashundhara R/A',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.8151,
                'lng' => 90.4312,
                'intro' => 'A master-planned private residential township featuring expansive divided avenues, underground utilities, top private universities (NSU, IUB), tertiary medical facilities (Evercare), and a secure, family-centric environment.',
                'meta_description' => 'Modern apartments, residential plots, and duplexes in Bashundhara Residential Area, Dhaka.',
                'image_path' => null,
            ],
            'uttara' => [
                'name' => 'Uttara',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.8759,
                'lng' => 90.3795,
                'intro' => 'A well-organized northern suburb planned in distinct sectors. Connected to central Dhaka via the MRT Line-6 Metro Rail and minutes from Hazrat Shahjalal International Airport, Uttara offers superb family convenience.',
                'meta_description' => 'Quality family apartments and commercial spaces near the Metro Rail in Uttara, Dhaka.',
                'image_path' => null,
            ],
            'mohakhali-dohs' => [
                'name' => 'Mohakhali DOHS',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.7776,
                'lng' => 90.3992,
                'intro' => 'A disciplined, secure gated society nestled between Banani, Gulshan, and Mohakhali. Favoured by corporate leaders and families for its tranquil atmosphere, lush community parks, and strict vehicular security.',
                'meta_description' => 'Secure, spacious residential apartments in Mohakhali DOHS, Dhaka.',
                'image_path' => null,
            ],
            'mirpur-dohs' => [
                'name' => 'Mirpur DOHS',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'lat' => 23.8340,
                'lng' => 90.3662,
                'intro' => 'One of Dhaka’s most scenic defense housing societies, featuring wide tree-lined boulevards, multiple community lakes, jogging tracks, sports fields, and modern apartment residences with low density.',
                'meta_description' => 'Calm, family-friendly apartments and homes in Mirpur DOHS, Dhaka.',
                'image_path' => null,
            ],
        ];

        $models = [];
        foreach ($areas as $slug => $data) {
            $models[$slug] = LocationArea::query()->updateOrCreate(
                ['slug' => $slug],
                [...$data, 'is_active' => true]
            );
        }

        return $models;
    }

    /**
     * Collect available high-res property images stored on disk.
     *
     * @return list<string>
     */
    protected function gatherMediaPool(): array
    {
        $files = glob(storage_path('app/public/media/2026/10/*.jpg'));
        if (empty($files)) {
            return [];
        }

        sort($files);

        return array_map(fn (string $file): string => 'media/2026/10/'.basename($file), $files);
    }

    /**
     * Attach gallery media records to a model from the available disk pool.
     *
     * @param  list<string>  $pool
     */
    protected function attachMedia(object $model, array &$pool, int $count = 2, string $labelPrefix = 'Property photo'): void
    {
        if (empty($pool)) {
            return;
        }

        $firstMedia = null;
        for ($i = 0; $i < $count; $i++) {
            $relPath = array_shift($pool);
            if (! $relPath) {
                break;
            }

            // Put it back to cycle if pool gets low
            $pool[] = $relPath;

            $media = Media::query()->updateOrCreate(
                [
                    'mediable_type' => $model::class,
                    'mediable_id' => $model->id,
                    'path' => $relPath,
                ],
                [
                    'collection' => 'gallery',
                    'disk' => 'public',
                    'original_disk' => 'public',
                    'original_path' => $relPath,
                    'original_filename' => basename($relPath),
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => 180000,
                    'width' => 1920,
                    'height' => 1080,
                    'sort_order' => $i + 1,
                    'is_public' => true,
                    'alt_texts' => ['en' => "{$labelPrefix} ".($i + 1)],
                ]
            );

            if ($i === 0) {
                $firstMedia = $media;
            }
        }

        if ($firstMedia && property_exists($model, 'featured_media_id')) {
            $model->update(['featured_media_id' => $firstMedia->id]);
        }
    }

    /**
     * Seed ongoing, completed, and upcoming real estate projects.
     *
     * @param  array<string, LocationArea>  $locations
     * @param  list<string>  $mediaPool
     * @return array<string, Project>
     */
    protected function seedProjects(User $owner, array $locations, array &$mediaPool): array
    {
        $amenityIds = Amenity::query()->pluck('id')->all();
        $sampleAmenities = array_slice($amenityIds, 0, 8);

        $projectsData = [
            'haven-residences-gulshan' => [
                'name' => 'Haven Residences Gulshan',
                'description' => 'A quiet, well-planned residential development in Gulshan 2 with generous natural daylight, landscaped courtyards, an infinity rooftop pool, and carefully specified interiors built for multigenerational families.',
                'development_stage' => 'ongoing',
                'property_category' => 'residential',
                'city' => 'Dhaka',
                'location_area_id' => $locations['gulshan']->id,
                'address' => 'Road 45, Gulshan 2, Dhaka',
                'lat' => 23.7925,
                'lng' => 90.4078,
                'developer_name' => 'Urban Haven Properties Ltd.',
                'completion_date' => now()->addMonths(14)->toDateString(),
                'handover_info' => 'Handover scheduled for Q4 2027 after interior detailing, lift installations, and final utility clearances.',
                'highlights' => ['Landscaped central courtyard', 'Two levels of basement parking', 'Infinity rooftop pool & fitness club', '100% full electrical backup generator'],
                'is_featured' => true,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            'the-verdant-banani' => [
                'name' => 'The Verdant Banani',
                'description' => 'A signature boutique residential project in Banani Block F featuring double-height entrance architecture, floor-to-ceiling double glazed windows, pre-wired smart home automation, and private elevator lobbies.',
                'development_stage' => 'ongoing',
                'property_category' => 'residential',
                'city' => 'Dhaka',
                'location_area_id' => $locations['banani']->id,
                'address' => 'Road 18, Block F, Banani, Dhaka',
                'lat' => 23.7937,
                'lng' => 90.4043,
                'developer_name' => 'Urban Haven Properties Ltd.',
                'completion_date' => now()->addMonths(8)->toDateString(),
                'handover_info' => 'Finishing stage in progress. European sanitary fittings, imported Italian marble, and central VRF air conditioning.',
                'highlights' => ['Only two residences per floor', 'Smart home automation system', 'Double height entrance lobby', 'EV charging station'],
                'is_featured' => true,
            ],
            'haven-heights-dhanmondi' => [
                'name' => 'Haven Heights Dhanmondi',
                'description' => 'Completed luxury lakeview residence overlooking Dhanmondi Lake. Completed to exceptional standards with dedicated parking, residents rooftop lounge, and 24/7 on-site facility management.',
                'development_stage' => 'completed',
                'property_category' => 'residential',
                'city' => 'Dhaka',
                'location_area_id' => $locations['dhanmondi']->id,
                'address' => 'Road 8/A, Dhanmondi, Dhaka',
                'lat' => 23.7461,
                'lng' => 90.3742,
                'developer_name' => 'Urban Haven Properties Ltd.',
                'completion_date' => now()->subMonths(6)->toDateString(),
                'handover_info' => 'Ready for immediate occupancy. Full utility connections (DESCO, WASA, Gas) operational.',
                'highlights' => ['Unobstructed lakefront vista', 'Rooftop community lounge & BBQ pavilion', "Children's play zone", 'Round-the-clock security and CCTV'],
                'is_featured' => true,
            ],
            'skyline-corporate-tower' => [
                'name' => 'Skyline Corporate Tower',
                'description' => 'A landmark Grade-A commercial tower situated directly on Gulshan Avenue. Offering column-free high-efficiency floor plates, LEED-certified green design, high-speed passenger lifts, and three basement parking levels.',
                'development_stage' => 'ongoing',
                'property_category' => 'commercial',
                'city' => 'Dhaka',
                'location_area_id' => $locations['gulshan']->id,
                'address' => 'Gulshan Avenue, Gulshan 1, Dhaka',
                'lat' => 23.7845,
                'lng' => 90.4150,
                'developer_name' => 'Urban Haven Properties Ltd.',
                'completion_date' => now()->addMonths(10)->toDateString(),
                'handover_info' => 'Bare shell handover with provisions for raised flooring, central chilled water HVAC, and dual fiber-optic backbone.',
                'highlights' => ['Double-glazed low-E thermal curtain wall', '4 high-speed passenger lifts + dedicated service lift', '3 levels of basement car parking', 'LEED-certified green building design'],
                'is_featured' => true,
            ],
            'haven-serenity-bashundhara' => [
                'name' => 'Haven Serenity Bashundhara',
                'description' => 'Pre-launch residential development situated in Bashundhara Block I, adjacent to a designated community park. Planned with generous green open spaces, earthquake-resistant RCC structure, and community amenities.',
                'development_stage' => 'upcoming',
                'property_category' => 'residential',
                'city' => 'Dhaka',
                'location_area_id' => $locations['bashundhara']->id,
                'address' => 'Block I, Bashundhara R/A, Dhaka',
                'lat' => 23.8180,
                'lng' => 90.4350,
                'developer_name' => 'Urban Haven Properties Ltd.',
                'completion_date' => now()->addMonths(30)->toDateString(),
                'handover_info' => 'Pre-launch bookings open with flexible 36-month installment plans and early-bird price advantages.',
                'highlights' => ['Adjacent to 50-ft wide green parkway', 'Earthquake-resistant RCC framed structure', 'Rooftop solar energy system', 'Resident fitness studio'],
                'is_featured' => false,
            ],
        ];

        $models = [];
        foreach ($projectsData as $slug => $data) {
            $project = Project::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    ...$data,
                    'amenity_ids' => $sampleAmenities,
                ]
            );

            $project->publicationState()->updateOrCreate(
                ['publishable_type' => Project::class, 'publishable_id' => $project->id],
                [
                    'status' => PublicationState::PUBLISHED,
                    'published_by' => $owner->id,
                    'published_at' => now(),
                ]
            );

            $this->attachMedia($project, $mediaPool, 3, $project->name);
            $models[$slug] = $project;
        }

        return $models;
    }

    /**
     * Seed realistic residential and commercial properties for sale and rent.
     *
     * @param  array<string, LocationArea>  $locations
     * @param  array<string, Project>  $projects
     * @param  list<string>  $mediaPool
     */
    protected function seedProperties(User $owner, array $locations, array $projects, array &$mediaPool): void
    {
        $types = PropertyType::query()->get()->keyBy('key');
        $amenityIds = Amenity::query()->pluck('id')->all();
        $apartmentAmenities = array_slice($amenityIds, 0, 7);
        $luxuryAmenities = $amenityIds;
        $commercialAmenities = array_slice($amenityIds, 2, 5);

        $listings = [
            [
                'slug' => 'gulshan-south-three-bed',
                'title' => 'South-facing 3 Bed in Gulshan 2',
                'reference' => 'UH-GUL3B',
                'description' => 'Bright south-facing luxury apartment featuring expansive living and dining spaces, three ensuite bedrooms, imported porcelain tile flooring, fitted kitchen, and private balconies overlooking landscaped grounds.',
                'location_area_id' => $locations['gulshan']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => $projects['haven-residences-gulshan']->id,
                'listing_type' => 'sale',
                'price' => 28500000,
                'price_basis' => 'total_sale',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'balconies' => 3,
                'parking_spaces' => 1,
                'area_value' => 1850,
                'floor_number' => 7,
                'facing' => 'south',
                'road_width_ft' => 30,
                'lat' => 23.7928,
                'lng' => 90.4081,
                'is_featured' => true,
                'display_priority' => 1,
                'amenity_ids' => $apartmentAmenities,
            ],
            [
                'slug' => 'gulshan-lakeview-penthouse',
                'title' => 'Lake-view Penthouse with Private Terrace',
                'reference' => 'UH-GULPH',
                'description' => 'Magnificent duplex penthouse perched on the top floors in Gulshan 1, commanding 360-degree panoramic views of Gulshan Lake. Features a 600 sqft private rooftop terrace, double-height living salon, master retreat with walk-in closet, and smart home lighting.',
                'location_area_id' => $locations['gulshan']->id,
                'property_type_id' => $types['penthouse']->id,
                'project_id' => null,
                'listing_type' => 'sale',
                'price' => 68000000,
                'price_basis' => 'total_sale',
                'bedrooms' => 4,
                'bathrooms' => 5,
                'balconies' => 4,
                'parking_spaces' => 2,
                'area_value' => 3450,
                'floor_number' => 14,
                'facing' => 'south-east',
                'road_width_ft' => 40,
                'lat' => 23.7885,
                'lng' => 90.4120,
                'is_featured' => true,
                'display_priority' => 2,
                'amenity_ids' => $luxuryAmenities,
            ],
            [
                'slug' => 'banani-corner-duplex',
                'title' => 'Corner Duplex in Banani Block F',
                'reference' => 'UH-BANDX',
                'description' => 'Architecturally designed corner duplex offering double-height ceilings, separate family lounge on the upper level, four lavish ensuite bedrooms, servant quarters with private entry, and dedicated 2-car basement parking.',
                'location_area_id' => $locations['banani']->id,
                'property_type_id' => $types['duplex']->id,
                'project_id' => $projects['the-verdant-banani']->id,
                'listing_type' => 'sale',
                'price' => 42000000,
                'price_basis' => 'total_sale',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'balconies' => 3,
                'parking_spaces' => 2,
                'area_value' => 2800,
                'floor_number' => 8,
                'facing' => 'south',
                'road_width_ft' => 35,
                'lat' => 23.7937,
                'lng' => 90.4043,
                'is_featured' => true,
                'display_priority' => 3,
                'amenity_ids' => $luxuryAmenities,
            ],
            [
                'slug' => 'dhanmondi-lake-two-bed',
                'title' => 'Lakeside 2 Bed in Dhanmondi 8/A',
                'reference' => 'UH-DHN2R',
                'description' => 'Serene lakefront apartment located in Dhanmondi Road 8/A. Freshly painted with hardwood floors, modern modular kitchen, built-in wardrobes, elevator access, and peaceful tree-lined surroundings.',
                'location_area_id' => $locations['dhanmondi']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => $projects['haven-heights-dhanmondi']->id,
                'listing_type' => 'rent',
                'price' => 75000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'balconies' => 2,
                'parking_spaces' => 1,
                'area_value' => 1250,
                'floor_number' => 4,
                'facing' => 'east',
                'road_width_ft' => 25,
                'lat' => 23.7461,
                'lng' => 90.3742,
                'is_featured' => false,
                'display_priority' => 4,
                'amenity_ids' => $apartmentAmenities,
            ],
            [
                'slug' => 'baridhara-diplomatic-residence',
                'title' => 'Diplomatic 4 Bed Residence in Baridhara',
                'reference' => 'UH-BAR4B',
                'description' => 'Prestigious high-security residence in Baridhara Diplomatic Zone. Boasts extensive security features, high boundary walls, CCTV surveillance, 24/7 backup power, wide foyer, and elegant formal dining for entertaining.',
                'location_area_id' => $locations['baridhara']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => null,
                'listing_type' => 'sale',
                'price' => 82000000,
                'price_basis' => 'total_sale',
                'bedrooms' => 4,
                'bathrooms' => 5,
                'balconies' => 4,
                'parking_spaces' => 2,
                'area_value' => 3200,
                'floor_number' => 6,
                'facing' => 'south',
                'road_width_ft' => 40,
                'lat' => 23.7998,
                'lng' => 90.4206,
                'is_featured' => true,
                'display_priority' => 5,
                'amenity_ids' => $luxuryAmenities,
            ],
            [
                'slug' => 'gulshan-diplomatic-suite-rent',
                'title' => 'Furnished Diplomatic Suite in Gulshan 2',
                'reference' => 'UH-GULFR',
                'description' => 'Tastefully furnished turnkey apartment ideal for expatriates and corporate executives. Includes Italian leather furniture, 65-inch 4K TV, fully equipped kitchen with dishwasher, high-speed Wi-Fi, and weekly housekeeping options.',
                'location_area_id' => $locations['gulshan']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => null,
                'listing_type' => 'rent',
                'price' => 160000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'balconies' => 2,
                'parking_spaces' => 1,
                'area_value' => 2100,
                'floor_number' => 9,
                'facing' => 'north-east',
                'is_furnished' => true,
                'road_width_ft' => 30,
                'lat' => 23.7960,
                'lng' => 90.4100,
                'is_featured' => true,
                'display_priority' => 6,
                'amenity_ids' => $luxuryAmenities,
            ],
            [
                'slug' => 'banani-luxury-duplex-rent',
                'title' => 'Serviced 3 Bed Duplex in Banani',
                'reference' => 'UH-BAN3R',
                'description' => 'Spectacular duplex residence in central Banani offering private elevator access, designer kitchen, soundproof glazing, rooftop barbecue deck access, and dedicated security guards.',
                'location_area_id' => $locations['banani']->id,
                'property_type_id' => $types['duplex']->id,
                'project_id' => null,
                'listing_type' => 'rent',
                'price' => 220000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => 3,
                'bathrooms' => 4,
                'balconies' => 3,
                'parking_spaces' => 2,
                'area_value' => 2600,
                'floor_number' => 7,
                'facing' => 'south',
                'is_furnished' => true,
                'road_width_ft' => 30,
                'lat' => 23.7915,
                'lng' => 90.4020,
                'is_featured' => false,
                'display_priority' => 7,
                'amenity_ids' => $luxuryAmenities,
            ],
            [
                'slug' => 'gulshan-commercial-floor',
                'title' => 'Fitted Commercial Floor, Skyline Tower',
                'reference' => 'UH-GULCOM',
                'description' => 'Grade-A fitted corporate office floor in Gulshan 1. Open workstation layout for 80+ employees, 4 executive director cabins, 2 conference rooms with video-conferencing setups, server room with precision cooling, and dedicated restrooms.',
                'location_area_id' => $locations['gulshan']->id,
                'property_type_id' => $types['commercial']->id,
                'project_id' => $projects['skyline-corporate-tower']->id,
                'listing_type' => 'rent',
                'price' => 380000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => null,
                'bathrooms' => 4,
                'balconies' => 0,
                'parking_spaces' => 4,
                'area_value' => 3200,
                'floor_number' => 11,
                'facing' => 'south',
                'road_width_ft' => 60,
                'lat' => 23.7845,
                'lng' => 90.4150,
                'is_featured' => false,
                'display_priority' => 8,
                'amenity_ids' => $commercialAmenities,
            ],
            [
                'slug' => 'banani-retail-showroom',
                'title' => 'Ground Floor Retail Showroom, Road 11',
                'reference' => 'UH-BANSHW',
                'description' => 'High-visibility premium showroom on prime Banani Road 11. Over 45 feet of clear glass street frontage with unmatched pedestrian and vehicular exposure. Ideal for luxury apparel, lifestyle flagships, or flagship banks.',
                'location_area_id' => $locations['banani']->id,
                'property_type_id' => $types['showroom']->id,
                'project_id' => null,
                'listing_type' => 'rent',
                'price' => 550000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => null,
                'bathrooms' => 2,
                'balconies' => 0,
                'parking_spaces' => 3,
                'area_value' => 2400,
                'floor_number' => 1,
                'facing' => 'west',
                'road_width_ft' => 50,
                'lat' => 23.7940,
                'lng' => 90.4060,
                'is_featured' => false,
                'display_priority' => 9,
                'amenity_ids' => $commercialAmenities,
            ],
            [
                'slug' => 'bashundhara-parkview-three-bed',
                'title' => 'Parkview 3 Bed in Bashundhara Block D',
                'reference' => 'UH-BAS3B',
                'description' => 'Bright family apartment overlooking a planned park in Bashundhara Block D. Quality construction, wide corridors, spacious master bedroom with dressing space, cross-ventilation, and dedicated underground parking slot.',
                'location_area_id' => $locations['bashundhara']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => null,
                'listing_type' => 'sale',
                'price' => 16500000,
                'price_basis' => 'total_sale',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'balconies' => 2,
                'parking_spaces' => 1,
                'area_value' => 1620,
                'floor_number' => 5,
                'facing' => 'south',
                'road_width_ft' => 30,
                'lat' => 23.8155,
                'lng' => 90.4320,
                'is_featured' => false,
                'display_priority' => 10,
                'amenity_ids' => $apartmentAmenities,
            ],
            [
                'slug' => 'bashundhara-prime-plot',
                'title' => '5 Katha South-Facing Plot in Bashundhara Block I',
                'reference' => 'UH-BASPLT',
                'description' => 'Prime 5 Katha rectangular residential plot situated on a 40-foot wide avenue in Bashundhara Block I. Completely filled, demarcated, boundary-walled, with mutation completed and ready for immediate building plan submission.',
                'location_area_id' => $locations['bashundhara']->id,
                'property_type_id' => $types['plot']->id,
                'project_id' => null,
                'listing_type' => 'sale',
                'price' => 47500000,
                'price_basis' => 'total_sale',
                'bedrooms' => null,
                'bathrooms' => null,
                'balconies' => 0,
                'parking_spaces' => 0,
                'area_value' => 3600,
                'area_unit' => 'sqft',
                'floor_number' => null,
                'facing' => 'south',
                'road_width_ft' => 40,
                'lat' => 23.8210,
                'lng' => 90.4380,
                'is_featured' => false,
                'display_priority' => 11,
                'amenity_ids' => [],
            ],
            [
                'slug' => 'mohakhali-executive-apartment',
                'title' => 'Calm Executive 2 Bed in Mohakhali DOHS',
                'reference' => 'UH-MHK2R',
                'description' => 'Peaceful residential living in secure Mohakhali DOHS. Features sunny rooms, modern tiled bathrooms, backup generator line, pleasant community surroundings, and quick access to both Banani and Jahangir Gate.',
                'location_area_id' => $locations['mohakhali-dohs']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => null,
                'listing_type' => 'rent',
                'price' => 70000,
                'price_basis' => 'monthly_rent',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'balconies' => 2,
                'parking_spaces' => 1,
                'area_value' => 1450,
                'floor_number' => 3,
                'facing' => 'south',
                'road_width_ft' => 30,
                'lat' => 23.7776,
                'lng' => 90.3992,
                'is_featured' => false,
                'display_priority' => 12,
                'amenity_ids' => $apartmentAmenities,
            ],
            [
                'slug' => 'uttara-metro-four-bed',
                'title' => 'Contemporary 4 Bed near Metro, Uttara 4',
                'reference' => 'UH-UTT4B',
                'description' => 'Spacious 4 bedroom home in Uttara Sector 4, just 400 metres from the MRT Line-6 station. Features wide family living, servant room with bath, double lift, generator backup, and high rental return potential.',
                'location_area_id' => $locations['uttara']->id,
                'property_type_id' => $types['apartment']->id,
                'project_id' => null,
                'listing_type' => 'sale',
                'price' => 19800000,
                'price_basis' => 'total_sale',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'balconies' => 3,
                'parking_spaces' => 1,
                'area_value' => 1950,
                'floor_number' => 6,
                'facing' => 'north-east',
                'road_width_ft' => 30,
                'lat' => 23.8759,
                'lng' => 90.3795,
                'is_featured' => false,
                'display_priority' => 13,
                'amenity_ids' => $apartmentAmenities,
            ],
        ];

        foreach ($listings as $data) {
            $property = Property::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'reference' => $data['reference'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'availability' => 'available',
                    'listing_type' => $data['listing_type'],
                    'price' => $data['price'],
                    'price_basis' => $data['price_basis'] ?? 'total_sale',
                    'currency' => 'BDT',
                    'area_value' => $data['area_value'],
                    'area_unit' => $data['area_unit'] ?? 'sqft',
                    'bedrooms' => $data['bedrooms'],
                    'bathrooms' => $data['bathrooms'],
                    'balconies' => $data['balconies'],
                    'parking_spaces' => $data['parking_spaces'],
                    'floor_number' => $data['floor_number'],
                    'facing' => $data['facing'],
                    'road_width_ft' => $data['road_width_ft'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'is_furnished' => $data['is_furnished'] ?? false,
                    'is_featured' => $data['is_featured'] ?? false,
                    'display_priority' => $data['display_priority'] ?? 99,
                    'amenity_ids' => $data['amenity_ids'],
                    'property_type_id' => $data['property_type_id'],
                    'location_area_id' => $data['location_area_id'],
                    'project_id' => $data['project_id'],
                ]
            );

            $property->publicationState()->updateOrCreate(
                ['publishable_type' => Property::class, 'publishable_id' => $property->id],
                [
                    'status' => PublicationState::PUBLISHED,
                    'published_by' => $owner->id,
                    'published_at' => now(),
                ]
            );

            // Seed inventory units for project properties
            if ($property->project_id) {
                foreach (['7A' => 'available', '7B' => 'reserved', '8A' => 'available'] as $unitNum => $unitStatus) {
                    Unit::query()->updateOrCreate(
                        ['property_id' => $property->id, 'unit_number' => $unitNum],
                        [
                            'price' => $property->price,
                            'status' => $unitStatus,
                            'notes' => "Unit {$unitNum} standard specifications",
                        ]
                    );
                }
            }

            $this->attachMedia($property, $mediaPool, 3, $property->title);
        }
    }

    /**
     * Seed authoritative Dhaka real estate articles and guides.
     *
     * @param  list<string>  $mediaPool
     */
    protected function seedArticles(User $owner, array &$mediaPool): void
    {
        $categories = [
            'buyer-guides' => 'Buyer Guides',
            'market-insights' => 'Market Insights',
            'neighbourhood-guides' => 'Neighbourhood Guides',
            'legal-finance' => 'Legal & Finance',
            'architecture-living' => 'Architecture & Living',
        ];

        $categoryModels = [];
        foreach ($categories as $slug => $name) {
            $categoryModels[$slug] = PostCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        $articles = [
            [
                'slug' => 'buying-an-apartment-in-dhaka-guide',
                'category_slug' => 'buyer-guides',
                'title' => 'The Definitive Guide to Buying an Apartment in Dhaka (2026 Edition)',
                'excerpt' => 'A practical walkthrough covering land ownership verification, RAJUK clearance, installment schedules, and hidden handover costs in Dhaka.',
                'body' => <<<'MARKDOWN'
Buying an apartment in Dhaka is often the single largest financial commitment an individual or family will make. With thousands of developments across Gulshan, Banani, Dhanmondi, and Uttara, separating solid investments from problematic projects requires disciplined due diligence.

### 1. Title Verification: CS, SA, RS, and City Jorip
Before placing any booking deposit, demand the chain of title deeds (Baya Deed) spanning at least 25 to 30 years. Ensure there are no disputes between CS, SA, RS, and City Jorip (BS) records. A clean Non-Encumbrance Certificate (NEC) from the relevant Sub-Registry office is essential.

### 2. RAJUK Approval and Setback Rules
Never rely merely on architectural 3D renders. Request the original RAJUK sanctioned building layout plan. Verify:
- Number of approved floors vs. advertised floors
- Mandatory setback boundaries on all four sides
- Proper allocation of basement car parking spaces
- Structural approval compliance with the Bangladesh National Building Code (BNBC)

### 3. Payment Schedules and Escrow Discipline
Reputable developers tie milestone payments to actual physical progress: piling, basement slab, roof casting, and plastering. Avoid agreements with calendar-based payment schedules that demand money irrespective of construction delays.

### 4. Hidden Handover Costs
Factor in an additional 8% to 12% above the unit cost for registration expenses, stamp duties, local government taxes, mutation fees, and utility security deposits (DESCO/DPDC, WASA, Titas Gas/LPG).
MARKDOWN,
            ],
            [
                'slug' => 'gulshan-banani-baridhara-comparison',
                'category_slug' => 'neighbourhood-guides',
                'title' => 'Gulshan vs. Banani vs. Baridhara: Selecting Your Ideal Neighbourhood',
                'excerpt' => 'Comparing Dhaka’s premier tri-neighbourhood enclaves across livability, diplomatic security, dining culture, and long-term capital appreciation.',
                'body' => <<<'MARKDOWN'
Dhaka's North-Eastern residential cluster — Gulshan, Banani, and Baridhara — represents the pinnacle of urban living in Bangladesh. While geographically adjacent, each enclave exhibits distinct characteristics.

### Baridhara: Unrivalled Security and Diplomatic Exclusivity
With controlled security barriers, private security patrols, and serene lakefront walks, Baridhara Diplomatic Zone offers unparalleled tranquility. Building heights are strictly regulated, creating low-density, peaceful residential living. It commands the highest price per square foot in the country.

### Gulshan: Cosmopolitan Elegance and High-End Living
Gulshan blends serene residential pockets in Gulshan-2 with high-street luxury retail, foreign missions, top hospitals (United Hospital), and international schools. For families seeking a prestigious address with immediate proximity to clubs and diplomatic events, Gulshan remains unmatched.

### Banani: Vibrant, Walkable, and Lifestyle-Centred
Banani is Dhaka’s most walkable and energetic upscale district. Road 11 is home to premier cafes, boutiques, and fitness studios. The inner residential blocks (Blocks C, D, F) provide tree-lined quiet avenues just minutes away from commercial convenience.
MARKDOWN,
            ],
            [
                'slug' => 'dhaka-land-registration-rajuk-mutation',
                'category_slug' => 'legal-finance',
                'title' => 'Understanding Land Registration, RAJUK Clearances, and Mutation in Bangladesh',
                'excerpt' => 'A clear guide to the statutory legal procedures, mutation certificates, stamp duties, and registration steps required when taking property possession.',
                'body' => <<<'MARKDOWN'
Navigating property legalities in Bangladesh can seem intimidating. Here is the step-by-step statutory process for completing your registration cleanly.

### Step 1: Sub-Registry Deed Execution (Saf-Kabala)
Upon full payment of the consideration price, the developer or seller executes the Saf-Kabala (sale deed) before the Sub-Registrar. Required fees include Stamp Duty (1.5%), Registration Fee (1%), Local Government Tax (2%–3%), and Advance Income Tax (AIT).

### Step 2: Mutation (Namzari)
Securing the sale deed is not the final step. You must file for Mutation with the Assistant Commissioner (Land) AC Land office to update the government Record of Rights (RoR) in your name. Mutation confirms you as the official taxpayer for the land share.

### Step 3: DCR and Khajna (Ground Rent) Payment
Once the mutation order is passed, pay the DCR (Duplicate Carbon Receipt) fee and receive your fresh Mutation Khatian. Pay annual Land Development Tax (Khajna) online to keep the land title active and incontrovertible.
MARKDOWN,
            ],
            [
                'slug' => 'southern-facing-importance-tropical-dhaka',
                'category_slug' => 'architecture-living',
                'title' => 'Why Southern Orientation Matters in Tropical Dhaka Architecture',
                'excerpt' => 'How orientation, natural cross-ventilation, and southern summer breezes significantly cut electricity bills and improve wellness in Dhaka homes.',
                'body' => <<<'MARKDOWN'
In tropical Dhaka, apartment orientation is far more than a personal preference — it directly dictates indoor comfort, natural lighting, and your monthly air conditioning bills.

### The Power of Southern Breezes (Dokkhina Batash)
Dhaka experiences warm, humid weather for nearly eight months of the year. The prevailing summer breeze sweeps in from the south and south-east (Bay of Bengal). South-facing apartments capture this airflow naturally, keeping living rooms and master suites cool with minimal mechanical cooling.

### Winter Sun and Daylight
During the brief winter months, the sun tracks lower along the southern sky, providing warm, gentle sunlight inside south-facing rooms. Conversely, west-facing apartments absorb scorching afternoon heat, creating a greenhouse effect that traps heat deep into the night.

At Urban Haven, our architectural layouts prioritize south and south-east alignments, cross-ventilation shafts, and deep verandas to ensure optimum natural ventilation.
MARKDOWN,
            ],
            [
                'slug' => 'mrt-metro-rail-impact-property-values',
                'category_slug' => 'market-insights',
                'title' => 'Dhaka Metro Rail (MRT Line-6) Impact on Residential Property Values',
                'excerpt' => 'An analysis of property appreciation, rental yield spikes, and commuter migration patterns along the Uttara-to-Motijheel MRT corridor.',
                'body' => <<<'MARKDOWN'
The commissioning and expansion of the MRT Line-6 Mass Rapid Transit network has triggered Dhaka’s most significant urban real estate revaluation in decades.

### Commute Times Collapsing from Hours to Minutes
Travel between Uttara North and Motijheel, which historically took up to two hours in peak traffic, has been reduced to a reliable 38-minute train ride. This predictability has altered where professionals choose to live.

### Value Appreciation Along Station Hubs
Properties located within a 500-metre to 1-kilometre radius of Mirpur, Pallabi, and Uttara metro stations have appreciated 25% to 40% faster than non-connected pockets. Rental demand in northern suburbs has surged as downtown banking and government employees prioritize rail access over downtown congestion.
MARKDOWN,
            ],
            [
                'slug' => 'commercial-real-estate-investment-dhaka',
                'category_slug' => 'market-insights',
                'title' => 'Commercial Real Estate in Dhaka: Rental Yields, Office Demand, and Best Practices',
                'excerpt' => 'Evaluating commercial Grade-A office returns, retail showroom vacancies, and the growing appetite for institutional lease covenants in Gulshan and Banani.',
                'body' => <<<'MARKDOWN'
While residential property offers capital safety, commercial real estate in Dhaka consistently generates superior cash-flow yields, typically ranging between 6.5% to 9% compared to 3% to 4.5% for residential apartments.

### Grade-A Corporate Space in High Demand
Multinational companies, banks, and technology leaders in Dhaka are vacating outdated standalone buildings in favour of certified Grade-A office towers offering centralized HVAC, multi-tier fire suppression systems, and high parking-to-area ratios.

### Lease Terms and Triple-Net Leases
Commercial tenancies typically lock in 5 to 9-year lease agreements with structured 10% to 15% rent escalations every two or three years. Investors seeking passive, reliable cash flow are increasingly directing capital toward commercial strata-title assets in Gulshan Avenue and Tejgaon Commercial Area.
MARKDOWN,
            ],
        ];

        foreach ($articles as $index => $data) {
            $cat = $categoryModels[$data['category_slug']];
            $post = Post::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'post_category_id' => $cat->id,
                    'excerpt' => $data['excerpt'],
                    'body' => $data['body'],
                    'author_label' => 'Urban Haven Research Desk',
                    'created_by' => $owner->id,
                    'updated_by' => $owner->id,
                ]
            );

            $post->publicationState()->updateOrCreate(
                ['publishable_type' => Post::class, 'publishable_id' => $post->id],
                [
                    'status' => PublicationState::PUBLISHED,
                    'published_by' => $owner->id,
                    'published_at' => now()->subDays($index * 3),
                ]
            );

            $this->attachMedia($post, $mediaPool, 1, $post->title);
        }
    }

    /**
     * Seed full CMS pages and modular CMS blocks for Homepage, About, Contact, and Articles.
     */
    protected function seedCmsBlocksAndPages(User $owner): void
    {
        $blocks = [
            'hero' => [
                'label' => 'Hero',
                'content' => [
                    'eyebrow' => '',
                    'title' => '',
                    'body' => '',
                    'cta_label' => '',
                    'cta_url' => '',
                    'slide_seconds' => 7,
                ],
            ],
            'home_housing' => [
                'label' => 'Housing & apartment listings',
                'content' => [
                    'title' => '',
                    'link_label' => '',
                    'limit' => 8,
                ],
            ],
            'home_projects' => [
                'label' => 'Featured developments',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'link_label' => '',
                    'limit' => 6,
                ],
            ],
            'home_featured' => [
                'label' => 'Featured properties',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'link_label' => '',
                    'limit' => 8,
                ],
            ],
            'home_trending' => [
                'label' => 'Trending properties',
                'content' => [
                    'title' => '',
                    'link_label' => '',
                    'limit' => 6,
                ],
            ],
            'home_locations' => [
                'label' => 'Explore by location',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'link_label' => '',
                    'limit' => 12,
                ],
            ],
            'home_types' => [
                'label' => 'Explore by property type',
                'content' => [
                    'title' => '',
                    'link_label' => '',
                ],
            ],
            'home_latest' => [
                'label' => 'Latest properties',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'link_label' => '',
                    'limit' => 8,
                ],
            ],
            'home_requirements' => [
                'label' => 'Property search callout',
                'content' => [
                    'eyebrow' => '',
                    'title' => '',
                    'body' => '',
                    'cta_label' => '',
                    'cta_url' => '',
                ],
            ],
            'about' => [
                'label' => 'Why Urban Haven',
                'content' => [
                    'title' => '',
                    'body' => '',
                    'link_label' => '',
                    'link_url' => '',
                ],
            ],
            'home_articles' => [
                'label' => 'Latest articles',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'link_label' => '',
                    'videos_title' => '',
                    'video_limit' => 3,
                ],
            ],
            'home_faq' => [
                'label' => 'Frequently asked questions',
                'content' => [
                    'title' => '',
                    'subtitle' => '',
                    'cta_label' => '',
                    'link_label' => '',
                    'limit' => 5,
                ],
            ],
            'about_hero' => [
                'label' => 'About Hero',
                'content' => [
                    'eyebrow' => '',
                    'title' => '',
                    'intro' => '',
                    'quote' => '',
                    'quote_kicker' => '',
                    'quote_label' => '',
                    'badge' => '',
                    'primary_cta' => '',
                    'primary_url' => '',
                    'secondary_cta' => '',
                    'secondary_url' => '',
                ],
            ],
            'about_story' => [
                'label' => 'Our Story',
                'content' => [
                    'eyebrow' => '',
                    'title' => '',
                    'cta_label' => '',
                    'cta_url' => '',
                    'stat_one_value' => '',
                    'stat_one_label' => '',
                    'stat_two_value' => '',
                    'stat_two_label' => '',
                    'stat_three_value' => '',
                    'stat_three_label' => '',
                    'stat_four_value' => '',
                    'stat_four_label' => '',
                ],
            ],
            'contact_hero' => [
                'label' => 'Contact Hero',
                'content' => [
                    'eyebrow' => '',
                    'title' => '',
                    'intro' => '',
                ],
            ],
            'contact_cta' => [
                'label' => 'Contact CTA',
                'content' => [
                    'title' => '',
                    'intro' => '',
                    'primary_cta' => '',
                    'primary_url' => '',
                    'secondary_cta' => '',
                    'secondary_url' => '',
                ],
            ],
            'articles_masthead' => [
                'label' => 'Articles Masthead',
                'content' => [
                    'eyebrow' => 'Urban Haven Journal',
                    'title' => 'Explore News, Insights and Guides',
                    'intro' => 'Ideas, places and market perspectives shaping how Bangladesh lives and invests.',
                ],
            ],
        ];

        foreach ($blocks as $key => $block) {
            CmsBlock::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $block['label'],
                    'content' => $block['content'],
                    'is_visible' => true,
                    'sort_order' => 0,
                    'updated_at' => now(),
                ]
            );
        }

        $pages = [
            [
                'slug' => 'about',
                'template' => 'about',
                'title' => 'About Urban Haven',
                'meta_title' => 'About Urban Haven Properties Ltd. | Dhaka Real Estate',
                'meta_description' => 'Learn about Urban Haven Properties Ltd., our philosophy, team, and commitment to transparent real estate development in Dhaka.',
                'body' => '',
            ],
            [
                'slug' => 'contact',
                'template' => 'contact',
                'title' => 'Contact Us',
                'meta_title' => 'Contact Urban Haven | Real Estate Inquiries & Office Visits',
                'meta_description' => 'Get in touch with the Urban Haven sales and client advisory team via telephone, WhatsApp, or our Banani corporate office.',
                'body' => '<p>Reach out to schedule an in-person site tour, discuss property valuation, or request custom advisory on developments across Dhaka.</p>',
            ],
            [
                'slug' => 'services',
                'template' => 'default',
                'title' => 'Our Services',
                'meta_title' => 'Real Estate Services | Urban Haven Properties',
                'meta_description' => 'Comprehensive property development, sales representation, legal due diligence, and tenancy management services in Dhaka.',
                'body' => '<h3>Property Advisory & Acquisition</h3><p>We provide personalized matchmaking for discerning homebuyers and institutional investors, curating residential and commercial options that align with your financial goals.</p><h3>Development & Architecture</h3><p>From site selection and feasibility studies to structural engineering and interior handover, we deliver quality buildings designed to endure.</p><h3>Title Search & Legal Documentation</h3><p>Our legal team facilitates end-to-end chain-of-title searches, mutation processing, sub-registry deed execution, and RAJUK plan verifications.</p>',
            ],
            [
                'slug' => 'privacy-policy',
                'template' => 'default',
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy | Urban Haven Properties Ltd.',
                'meta_description' => 'How Urban Haven collects, uses, and safeguards your personal data when browsing our properties and submitting inquiries.',
                'body' => '<p>Urban Haven Properties Ltd. ("Urban Haven", "we", "us") values your privacy. This Privacy Policy outlines how we collect, store, and process personal information when you use our website and services.</p><h4>Information We Collect</h4><p>We collect contact details (such as your name, telephone number, and email address) submitted voluntarily via our inquiry and site visit booking forms. We also gather anonymized browsing analytics to measure site usage.</p><h4>How We Use Your Data</h4><p>Your details are used solely to facilitate your property inquiry, organize site visits, and provide updates regarding relevant listings. We never sell or lease your personal information to third-party marketing firms.</p>',
            ],
            [
                'slug' => 'terms-of-service',
                'template' => 'default',
                'title' => 'Terms of Service',
                'meta_title' => 'Terms of Service | Urban Haven Properties Ltd.',
                'meta_description' => 'Terms and conditions governing the use of the Urban Haven website and property information.',
                'body' => '<p>Welcome to Urban Haven Properties Ltd. By accessing or browsing our website, you agree to comply with and be bound by the following terms and conditions.</p><h4>Accuracy of Information</h4><p>While Urban Haven takes every reasonable precaution to ensure property specifications, sizes, prices, and availability are accurate at the time of publication, listings are subject to prior sale, lease, or price modifications without prior notice.</p><h4>Intellectual Property</h4><p>All photographs, architectural renders, floor plans, and editorial content published on this website are the intellectual property of Urban Haven Properties Ltd. and protected under Bangladesh copyright laws.</p>',
            ],
        ];

        foreach ($pages as $page) {
            $model = CmsPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'template' => $page['template'] ?? 'default',
                    'title' => $page['title'],
                    'meta_title' => $page['meta_title'] ?? $page['title'],
                    'meta_description' => $page['meta_description'] ?? null,
                    'body' => $page['body'],
                    'status' => 'published',
                    'created_by' => $owner->id,
                    'updated_by' => $owner->id,
                ]
            );

            $model->publicationState()->updateOrCreate(
                ['publishable_type' => CmsPage::class, 'publishable_id' => $model->id],
                [
                    'status' => PublicationState::PUBLISHED,
                    'published_by' => $owner->id,
                    'published_at' => now(),
                ]
            );
        }
    }

    /**
     * Seed navigation items for Header and Footer menus.
     */
    protected function seedMenus(): void
    {
        $headerItems = [
            ['label' => 'Properties', 'url' => '/properties', 'sort_order' => 1],
            ['label' => 'Projects', 'url' => '/projects', 'sort_order' => 2],
            ['label' => 'Locations', 'url' => '/properties#locations', 'sort_order' => 3],
            ['label' => 'About Us', 'url' => '/about', 'sort_order' => 4],
            ['label' => 'Journal', 'url' => '/articles', 'sort_order' => 5],
            ['label' => 'Contact', 'url' => '/contact', 'sort_order' => 6],
        ];

        foreach ($headerItems as $item) {
            MenuItem::query()->updateOrCreate(
                ['location' => 'header', 'label' => $item['label']],
                [
                    'url' => $item['url'],
                    'sort_order' => $item['sort_order'],
                    'opens_new_tab' => false,
                    'is_visible' => true,
                ]
            );
        }

        $footerItems = [
            ['label' => 'Explore Properties', 'url' => '/properties', 'sort_order' => 1],
            ['label' => 'Featured Projects', 'url' => '/projects', 'sort_order' => 2],
            ['label' => 'Market Journal', 'url' => '/articles', 'sort_order' => 3],
            ['label' => 'Common Questions', 'url' => '/faq', 'sort_order' => 4],
            ['label' => 'About Urban Haven', 'url' => '/about', 'sort_order' => 5],
            ['label' => 'Contact Sales Desk', 'url' => '/contact', 'sort_order' => 6],
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy', 'sort_order' => 7],
            ['label' => 'Terms of Service', 'url' => '/terms-of-service', 'sort_order' => 8],
        ];

        foreach ($footerItems as $item) {
            MenuItem::query()->updateOrCreate(
                ['location' => 'footer', 'label' => $item['label']],
                [
                    'url' => $item['url'],
                    'sort_order' => $item['sort_order'],
                    'opens_new_tab' => false,
                    'is_visible' => true,
                ]
            );
        }
    }

    /**
     * Seed sample CRM leads, inquiries, and site visit bookings for local/testing environments.
     */
    protected function seedDemoLeads(User $owner): void
    {
        $properties = Property::query()->published()->take(4)->get();
        if ($properties->isEmpty()) {
            return;
        }

        $prop1 = $properties->get(0);
        $prop2 = $properties->get(1);

        $demoLeads = [
            [
                'type' => 'property_inquiry',
                'name' => 'Kazi Farhan',
                'phone' => '+8801722114455',
                'email' => 'farhan.kazi@example.com',
                'property_id' => $prop1?->id,
                'source' => 'website_property_page',
                'preferred_contact' => 'whatsapp',
                'status' => 'new',
                'priority' => 'high',
                'message' => 'Interested in inspecting the 3 bed unit this Saturday morning. Please confirm availability and parking details.',
                'consent_given' => true,
                'is_unread' => true,
            ],
            [
                'type' => 'visit_request',
                'name' => 'Dr. Nusrat Jahan',
                'phone' => '+8801819332211',
                'email' => 'nusrat.jahan@example.com',
                'property_id' => $prop2?->id,
                'source' => 'website_visit_modal',
                'preferred_contact' => 'phone',
                'status' => 'visit_scheduled',
                'priority' => 'high',
                'message' => 'Looking for a lakeside home for family relocation. Preferred visit on Friday at 3:30 PM.',
                'consent_given' => true,
                'is_unread' => false,
                'assigned_to' => $owner->id,
            ],
            [
                'type' => 'general_contact',
                'name' => 'Engr. Mahmudul Hasan',
                'phone' => '+8801911445566',
                'email' => 'mahmud.hasan@example.com',
                'property_id' => null,
                'source' => 'contact_page',
                'preferred_contact' => 'email',
                'status' => 'contacted',
                'priority' => 'medium',
                'message' => 'Inquiring about upcoming residential projects in Bashundhara Block I with flexible payment installments.',
                'consent_given' => true,
                'is_unread' => false,
            ],
        ];

        foreach ($demoLeads as $data) {
            $lead = Lead::query()->updateOrCreate(
                ['phone' => $data['phone']],
                [
                    ...$data,
                    'phone_hash' => hash('sha256', $data['phone']),
                    'submission_token' => Str::random(32),
                ]
            );

            if ($lead->status === 'visit_scheduled') {
                SiteVisitRequest::query()->updateOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'property_id' => $lead->property_id,
                        'preferred_at' => now()->addDays(2)->setHour(15)->setMinute(30),
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'confirmed_by' => $owner->id,
                        'assigned_to' => $owner->id,
                        'notes' => 'Confirmed visit slot via WhatsApp.',
                    ]
                );

                LeadFollowUp::query()->updateOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'user_id' => $owner->id,
                        'action_type' => 'site_visit',
                        'priority' => 'high',
                        'status' => 'pending',
                        'notes' => 'Accompany client during lakeview penthouse walkthrough.',
                        'scheduled_at' => now()->addDays(2)->setHour(15)->setMinute(30),
                    ]
                );
            }
        }
    }
}
