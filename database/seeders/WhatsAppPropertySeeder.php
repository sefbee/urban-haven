<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Media;
use App\Models\PublicationState;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class WhatsAppPropertySeeder extends Seeder
{
    public function run(): void
    {
        // First, let's clear existing properties and their media
        Property::query()->delete();
        Media::where('mediable_type', Property::class)->delete();

        // Ensure required taxonomy exists
        $apartmentType = PropertyType::firstOrCreate(
            ['key' => 'apartment'],
            ['label' => 'Apartment', 'field_profile' => 'apartment', 'category' => 'residential', 'is_active' => true]
        );

        $gulshan1 = LocationArea::firstOrCreate(['slug' => 'gulshan-1'], ['name' => 'Gulshan 1', 'city' => 'Dhaka', 'country' => 'Bangladesh', 'is_active' => true]);
        $gulshan2 = LocationArea::firstOrCreate(['slug' => 'gulshan-2'], ['name' => 'Gulshan 2', 'city' => 'Dhaka', 'country' => 'Bangladesh', 'is_active' => true]);

        $gym = Amenity::firstOrCreate(['key' => 'gym'], ['label' => 'Fully Equipped Gym', 'icon_class' => 'ph-barbell', 'is_active' => true]);
        $clubhouse = Amenity::firstOrCreate(['key' => 'clubhouse'], ['label' => 'Exclusive Clubhouse', 'icon_class' => 'ph-club', 'is_active' => true]);
        $swimmingPool = Amenity::firstOrCreate(['key' => 'swimming_pool'], ['label' => 'Swimming Pool', 'icon_class' => 'ph-swimming-pool', 'is_active' => true]);
        $furnished = Amenity::firstOrCreate(['key' => 'furnished'], ['label' => 'Fully Furnished', 'icon_class' => 'ph-armchair', 'is_active' => true]);

        $properties = [
            [
                'title' => 'LUXURY 4-BEDROOM APARTMENT FOR RENT | GULSHAN 1',
                'description' => "Experience refined living in a spacious 3,500 SFT residence in one of Gulshan’s 1 prestigious residential addresses.\n\n✨ 4 Bedrooms\n✨ Premium residential environment\n✨ Fully Equipped Gym\n✨ Exclusive Clubhouse\n✨ Excellent location in Gulshan 2\n\n📞 Call or WhatsApp: 01755-015959 for details & viewing",
                'area_value' => 3500,
                'area_unit' => 'sqft',
                'listing_type' => 'rent',
                'property_type_id' => $apartmentType->id,
                'location_area_id' => $gulshan1->id,
                'amenity_ids' => [$gym->id, $clubhouse->id],
                'bedrooms' => 4,
            ],
            [
                'title' => 'LUXURY 3-BEDROOM APARTMENT FOR RENT | GULSHAN 2',
                'description' => "Experience refined living in a spacious 4,000 SFT residence in one of Gulshan’s 2 prestigious residential addresses.\n\n✨ 3 Bedrooms\n✨ Premium residential environment\n✨ Fully Equipped Gym\n✨ Exclusive Clubhouse\n✨ Excellent location in Gulshan 2\n\n📞 Call or WhatsApp: 01755-015959 for details & viewing",
                'area_value' => 4000,
                'area_unit' => 'sqft',
                'listing_type' => 'rent',
                'property_type_id' => $apartmentType->id,
                'location_area_id' => $gulshan2->id,
                'amenity_ids' => [$gym->id, $clubhouse->id],
                'bedrooms' => 3,
            ],
            [
                'title' => 'LUXURY 4-BEDROOM APARTMENT FOR RENT | GULSHAN 2',
                'description' => "Experience refined living in a spacious 4,200 SFT residence in one of Gulshan’s 2 prestigious residential addresses.\n💥Full Furnish\n✨ 4 Bedrooms\n✨ Premium residential environment\n✨ Fully Equipped Gym\n✨ Exclusive Clubhouse\n✨ Excellent location in Gulshan 2\n\n📞 Call or WhatsApp: 01755-015959 for details & viewing",
                'area_value' => 4200,
                'area_unit' => 'sqft',
                'listing_type' => 'rent',
                'property_type_id' => $apartmentType->id,
                'location_area_id' => $gulshan2->id,
                'amenity_ids' => [$gym->id, $clubhouse->id, $furnished->id],
                'bedrooms' => 4,
            ],
            [
                'title' => 'LUXURY 3-BEDROOM APARTMENT FOR RENT | GULSHAN 2',
                'description' => "Experience refined living in a spacious 2300 SFT residence in one of Gulshan’s 2 prestigious residential addresses.\n💥Full Furnish\n✨ 3 Bedrooms\n✨ Premium residential environment\n✨ Fully Equipped Gym\n✨ Swimming Pool\n✨ Exclusive Clubhouse\n✨ Excellent location in Gulshan 2\n\n📞 Call or WhatsApp: 01755-015959 for details & viewing",
                'area_value' => 2300,
                'area_unit' => 'sqft',
                'listing_type' => 'rent',
                'property_type_id' => $apartmentType->id,
                'location_area_id' => $gulshan2->id,
                'amenity_ids' => [$gym->id, $clubhouse->id, $swimmingPool->id, $furnished->id],
                'bedrooms' => 3,
            ]
        ];

        // Using relative paths inside the repository so it works on live server
        $imageDir = database_path('seeders/images');
        $uploadedImages = [
            $imageDir . '/media_1792225795486_daa82e0c.jpg',
            $imageDir . '/media_1792225795496_c50008a9.jpg',
            $imageDir . '/media_1792225795503_3b01e429.jpg',
            $imageDir . '/media_1792225795525_aac967f8.jpg',
            $imageDir . '/media_1792225795544_b97516a8.jpg',
        ];

        $mediaData = [];
        Storage::disk('public')->makeDirectory('properties');
        
        foreach ($uploadedImages as $index => $sourcePath) {
            if (File::exists($sourcePath)) {
                $filename = basename($sourcePath);
                $destPath = 'properties/' . $filename;
                Storage::disk('public')->put($destPath, File::get($sourcePath));
                
                $mediaData[] = [
                    'collection' => 'gallery',
                    'disk' => 'public',
                    'path' => $destPath,
                    'original_filename' => $filename,
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => File::size($sourcePath),
                    'sort_order' => $index,
                ];
            } else {
                echo "Warning: Image not found at {$sourcePath}\n";
            }
        }

        foreach ($properties as $i => $data) {
            $data['slug'] = Str::slug($data['title'] . '-' . uniqid());
            $data['reference'] = 'REF-' . strtoupper(Str::random(6));
            $property = Property::create($data);
            
            // Setting the publication status to PUBLISHED immediately
            $property->publicationState()->update([
                'status' => PublicationState::PUBLISHED,
                'published_at' => now(),
            ]);
            
            // Attach all 5 images to each property's gallery
            foreach ($mediaData as $m) {
                $property->media()->create($m);
            }
        }
    }
}
