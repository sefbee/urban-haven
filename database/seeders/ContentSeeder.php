<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Seed full application content for both live and local environments.
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            ReferenceDataSeeder::class,
            CatalogSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
