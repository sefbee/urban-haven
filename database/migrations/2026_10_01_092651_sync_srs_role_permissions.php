<?php

use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Existing installations must lose editor publish rights without a manual reseed.
     */
    public function up(): void
    {
        (new RolesPermissionsSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
