<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $groupId = DB::table('menu_items')
            ->where('location', 'footer')
            ->whereNull('parent_id')
            ->where('label', 'Explore')
            ->where('url', '#')
            ->value('id');

        $groupId ??= DB::table('menu_items')->insertGetId([
            'location' => 'footer',
            'parent_id' => null,
            'label' => 'Explore',
            'url' => '#',
            'opens_new_tab' => false,
            'sort_order' => ((int) DB::table('menu_items')->where('location', 'footer')->max('sort_order')) + 10,
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            ['Properties for sale', '/properties?listing_type=sale'],
            ['Properties for rent', '/properties?listing_type=rent'],
            ['Map', '/map'],
            ['Shortlist', '/shortlist'],
            ['Compare', '/compare'],
        ] as $index => [$label, $url]) {
            if (DB::table('menu_items')->where('location', 'footer')->where('url', $url)->exists()) {
                continue;
            }

            DB::table('menu_items')->insert([
                'location' => 'footer',
                'parent_id' => $groupId,
                'label' => $label,
                'url' => $url,
                'opens_new_tab' => false,
                'sort_order' => ($index + 1) * 10,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $groupId = DB::table('menu_items')
            ->where('location', 'footer')
            ->whereNull('parent_id')
            ->where('label', 'Explore')
            ->where('url', '#')
            ->value('id');

        if ($groupId) {
            DB::table('menu_items')
                ->where('parent_id', $groupId)
                ->whereIn('url', ['/properties?listing_type=sale', '/properties?listing_type=rent', '/map', '/shortlist', '/compare'])
                ->delete();

            if (! DB::table('menu_items')->where('parent_id', $groupId)->exists()) {
                DB::table('menu_items')->where('id', $groupId)->delete();
            }
        }
    }
};
