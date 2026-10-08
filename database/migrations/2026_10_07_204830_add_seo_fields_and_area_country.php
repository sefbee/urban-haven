<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_overrides', function (Blueprint $table) {
            $table->string('focus_keyword', 80)->nullable()->after('seoable_id');
            $table->string('gsc_code', 100)->nullable()->after('og_image_path');
        });

        Schema::table('location_areas', function (Blueprint $table) {
            $table->string('country', 80)->default('Bangladesh')->after('slug');
            $table->index(['country', 'city']);
        });

        $this->moveMetaIntoSeoOverrides('cms_pages', 'App\Models\CmsPage', ['meta_title', 'meta_description']);
        $this->moveMetaIntoSeoOverrides('location_areas', 'App\Models\LocationArea', ['meta_description']);
    }

    public function down(): void
    {
        Schema::table('location_areas', function (Blueprint $table) {
            $table->dropIndex(['country', 'city']);
            $table->dropColumn('country');
        });

        Schema::table('seo_overrides', function (Blueprint $table) {
            $table->dropColumn(['focus_keyword', 'gsc_code']);
        });
    }

    /**
     * Search engine copy now lives in one place, so the legacy per-table columns are copied
     * into seo_overrides and cleared. Fields already set on an override win.
     *
     * @param  list<string>  $columns
     */
    private function moveMetaIntoSeoOverrides(string $table, string $type, array $columns): void
    {
        DB::table($table)->orderBy('id')->each(function (object $row) use ($table, $type, $columns): void {
            $values = array_filter(array_map(fn (string $column): ?string => filled($row->{$column} ?? null) ? $row->{$column} : null, array_combine($columns, $columns)));

            if ($values === []) {
                return;
            }

            $existing = DB::table('seo_overrides')->where('seoable_type', $type)->where('seoable_id', $row->id)->first();

            if ($existing) {
                $merge = array_filter($values, fn (string $value, string $column): bool => blank($existing->{$column}), ARRAY_FILTER_USE_BOTH);

                if ($merge !== []) {
                    DB::table('seo_overrides')->where('id', $existing->id)->update($merge + ['updated_at' => now()]);
                }
            } else {
                DB::table('seo_overrides')->insert($values + ['seoable_type' => $type, 'seoable_id' => $row->id, 'noindex' => false, 'updated_at' => now()]);
            }

            DB::table($table)->where('id', $row->id)->update(array_fill_keys($columns, null));
        });

        if (Schema::hasColumn($table, 'pending_changes')) {
            DB::table($table)->whereNotNull('pending_changes')->orderBy('id')->each(function (object $row) use ($table, $columns): void {
                $pending = json_decode((string) $row->pending_changes, true);

                if (! is_array($pending) || array_intersect_key($pending, array_flip($columns)) === []) {
                    return;
                }

                $pending = array_diff_key($pending, array_flip($columns));
                DB::table($table)->where('id', $row->id)->update(['pending_changes' => $pending === [] ? null : json_encode($pending)]);
            });
        }
    }
};
