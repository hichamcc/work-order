<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaponService
{
    const CACHE_KEY = 'mapon.units';

    /**
     * Fetch the unit list from Mapon, keyed by cleaned plate.
     *
     * Units are indexed under every plate variant Mapon exposes (label, number,
     * reg_number) so a truck can be found regardless of which one was entered.
     *
     * @return array<string, array>
     */
    public function units(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        $minutes = config('services.mapon.cache_minutes', 10);

        return Cache::remember(self::CACHE_KEY, now()->addMinutes($minutes), function () {
            return $this->fetchUnits();
        });
    }

    /**
     * Look up a single truck by its plate/number.
     */
    public function unit(string $plate, bool $fresh = false): ?array
    {
        return $this->units($fresh)[$this->normalisePlate($plate)] ?? null;
    }

    /**
     * A flat, sorted list of trucks for dropdowns.
     *
     * Mapon is indexed under several aliases per unit, so results are collapsed
     * back down to one entry per unit id.
     *
     * @return array<int, array{number: string, label: string, km: int|null, km_source: string|null}>
     */
    public function unitsForSelect(bool $fresh = false): array
    {
        $seen = [];

        foreach ($this->units($fresh) as $unit) {
            $id = $unit['unit_id'] ?? $unit['number'] ?? null;

            if ($id === null || isset($seen[$id])) {
                continue;
            }

            $number = $unit['number'] ?? $unit['label'] ?? $unit['reg_number'] ?? null;

            if (! $number) {
                continue;
            }

            $reading = $this->readingFor($unit);

            $seen[$id] = [
                'number' => $number,
                'label' => $unit['label'] ?? $number,
                'km' => $reading['km'],
                'km_source' => $reading['source'],
            ];
        }

        $units = array_values($seen);

        usort($units, fn ($a, $b) => strcasecmp($a['number'], $b['number']));

        return $units;
    }

    /**
     * Current odometer for a unit.
     *
     * Prefers the CAN bus odometer, which is the truck's real dashboard value,
     * and falls back to the GPS-derived distance when the bus is unavailable.
     *
     * @return array{km: int|null, source: string|null}
     */
    public function readingFor(array $unit): array
    {
        if (! empty($unit['can']['odom']['value'])) {
            return [
                'km' => (int) round((float) $unit['can']['odom']['value']),
                'source' => 'can',
            ];
        }

        $gpsMeters = isset($unit['mileage']) ? (int) $unit['mileage'] : 0;

        if ($gpsMeters > 0) {
            return [
                'km' => (int) round($gpsMeters / 1000),
                'source' => 'gps',
            ];
        }

        return ['km' => null, 'source' => null];
    }

    /**
     * Current odometer for a truck by plate.
     *
     * @return array{km: int|null, source: string|null}
     */
    public function readingForPlate(string $plate, bool $fresh = false): array
    {
        $unit = $this->unit($plate, $fresh);

        return $unit ? $this->readingFor($unit) : ['km' => null, 'source' => null];
    }

    /**
     * Plates are compared without spaces or case so "AB 1234" matches "ab1234".
     */
    public function normalisePlate(string $plate): string
    {
        return strtoupper(preg_replace('/\s+/', '', $plate));
    }

    /**
     * @return array<string, array>
     */
    protected function fetchUnits(): array
    {
        $key = config('services.mapon.key');

        if (empty($key)) {
            Log::warning('Mapon API key is not configured; returning no units.');

            return [];
        }

        try {
            $response = Http::timeout(20)
                ->get(rtrim(config('services.mapon.base_url'), '/').'/unit/list.json', [
                    'key' => $key,
                    'include' => ['fuel', 'fuel_tank', 'can'],
                ]);
        } catch (\Throwable $e) {
            Log::error('Mapon request failed: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::error('Mapon returned HTTP '.$response->status());

            return [];
        }

        $units = $response->json('data.units');

        if (empty($units)) {
            Log::warning('Mapon returned no units. Check the API key.');

            return [];
        }

        $indexed = [];

        foreach ($units as $unit) {
            foreach (['label', 'number', 'reg_number'] as $field) {
                if (! empty($unit[$field])) {
                    $indexed[$this->normalisePlate($unit[$field])] = $unit;
                }
            }
        }

        return $indexed;
    }
}
