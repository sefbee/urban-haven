<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PropertyMetricDaily extends Model
{
    public const METRICS = ['views', 'cta_clicks'];

    public $timestamps = false;

    protected $table = 'property_metrics_daily';

    protected $fillable = ['property_id', 'date', 'views', 'cta_clicks'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public static function record(int $propertyId, string $metric): void
    {
        if (! in_array($metric, self::METRICS, true)) {
            return;
        }

        $date = now()->toDateString();

        DB::table('property_metrics_daily')->upsert(
            [['property_id' => $propertyId, 'date' => $date, $metric => 1]],
            ['property_id', 'date'],
            [$metric => DB::raw($metric.' + 1')],
        );
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
