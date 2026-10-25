<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trailer extends Model
{
    protected $fillable = [
        'plate_number',
        'label',
        'needs_service',
        'service_note',
        'flagged_at',
        'flagged_by',
        'work_order_id',
        'last_serviced_at',
        'is_active',
    ];

    protected $casts = [
        'needs_service' => 'boolean',
        'is_active' => 'boolean',
        'flagged_at' => 'datetime',
        'last_serviced_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function flaggedBy()
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function services()
    {
        return $this->hasMany(TrailerService::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedingService($query)
    {
        return $query->where('needs_service', true);
    }

    /**
     * Plates are compared without spaces or case, so "AN 84 35" matches "an8435".
     */
    public static function normalisePlate(string $plate): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($plate)));
    }

    /**
     * Pull the registration out of however the workshop wrote it.
     *
     * The list mixes the plate with owners and specs: "Ernø FJ 4743",
     * "FJ 47 43 DFDS" and "FJ4743" are all one trailer. A Danish plate is two
     * letters and four digits, so that pattern is lifted out wherever it sits
     * and everything else is kept only as a label.
     *
     * Returns null when no plate can be found, and the caller falls back to the
     * normalised text so the row is still imported rather than dropped.
     */
    public static function extractPlate(string $text): ?string
    {
        $stripped = preg_replace('/[^A-Z0-9]/', '', strtoupper($text));

        if ($stripped === '' || $stripped === null) {
            return null;
        }

        return preg_match('/([A-Z]{2}\d{4})/', $stripped, $matches)
            ? $matches[1]
            : null;
    }

    /**
     * The key a row should be stored under: its plate, or the cleaned text.
     */
    public static function keyFor(string $text): string
    {
        return static::extractPlate($text) ?? static::normalisePlate($text);
    }

    /**
     * What to show in the interface.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->label ?: $this->plate_number;
    }
}
