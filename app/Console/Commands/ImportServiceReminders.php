<?php

namespace App\Console\Commands;

use App\Models\TruckServiceAlert;
use App\Services\MaponService;
use App\Services\SpreadsheetReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportServiceReminders extends Command
{
    protected $signature = 'trucks:import-service-reminders
                            {file : Path to the Mapon service reminder export (.xlsx or .csv)}
                            {--force : Write the rows. Without it the command only reports what it would do}';

    protected $description = 'Import Mapon service reminders as the odometer each truck is next due for an oil service at';

    /**
     * The export writes the same service under several spellings.
     */
    protected array $oilTitles = ['oil change', 'olieservice', 'oil service', 'oliservice'];

    public function handle(MaponService $mapon, SpreadsheetReader $reader)
    {
        $path = $this->argument('file');
        $write = $this->option('force');

        if (! is_readable($path)) {
            $this->error("File not found: {$path}");

            return 1;
        }

        $rows = $reader->rows($path);

        if (empty($rows)) {
            $this->error('The sheet has no rows.');

            return 1;
        }

        $this->info(count($rows).' rows read from '.basename($path));

        $units = $mapon->units(fresh: true);

        if (empty($units)) {
            $this->error('Mapon returned no units. Check MAPON_API_KEY.');

            return 1;
        }

        $this->info(count($units).' Mapon plate keys available.');
        $this->newLine();

        // Keep the newest reminder per truck: the sheet holds several rows for
        // some vehicles and only the current one describes the next service.
        $pending = [];
        $skipped = ['not_oil' => 0, 'completed' => 0, 'no_mileage' => 0, 'unmatched' => [], 'no_reading' => []];

        foreach ($rows as $row) {
            $plate = $row['Vehicle number'] ?? '';
            $title = strtolower($row['Title'] ?? '');
            $status = $row['Status'] ?? '';
            $remaining = $row['Remaining mileage'] ?? '';
            $sheetNote = trim($row['Notes'] ?? '');

            if ($plate === '') {
                continue;
            }

            if (! $this->isOilService($title)) {
                $skipped['not_oil']++;
                continue;
            }

            // Completed reminders carry no remaining mileage, so there is no
            // due point to derive from them.
            if ($status === 'Completed' || ! is_numeric(str_replace([' ', ','], '', $remaining))) {
                $status === 'Completed' ? $skipped['completed']++ : $skipped['no_mileage']++;
                continue;
            }

            $resolved = $mapon->resolvePlate($plate);

            if (! $resolved['unit']) {
                $skipped['unmatched'][$plate] = true;
                continue;
            }

            $reading = $mapon->readingFor($resolved['unit']);

            if ($reading['km'] === null) {
                $skipped['no_reading'][$plate] = true;
                continue;
            }

            $remainingKm = (int) round((float) str_replace([' ', ','], '', $remaining));
            $truckNumber = $resolved['unit']['number'] ?? $resolved['key'];

            // Mapon reports how far is left, so the absolute due point is the
            // current odometer plus that remainder. Overdue rows are negative
            // and correctly land below the current reading.
            $dueAtKm = $reading['km'] + $remainingKm;

            $pending[$truckNumber] = [
                'truck_number' => $truckNumber,
                'source_plate' => $plate,
                'matched_on' => $resolved['matched_on'],
                'current_km' => $reading['km'],
                'km_source' => $reading['source'],
                'remaining_km' => $remainingKm,
                'due_at_km' => max(0, $dueAtKm),
                'overdue' => $remainingKm <= 0,
                'note' => $sheetNote,
            ];
        }

        if (empty($pending)) {
            $this->error('Nothing to import.');
            $this->reportSkips($skipped);

            return 1;
        }

        $this->table(
            ['Truck', 'Current KM', 'Remaining', 'Due at KM', 'State', 'Note'],
            collect($pending)
                ->sortBy('remaining_km')
                ->map(fn ($r) => [
                    $r['truck_number'],
                    number_format($r['current_km']),
                    number_format($r['remaining_km']),
                    number_format($r['due_at_km']),
                    $r['overdue'] ? 'OVERDUE' : 'ok',
                    \Illuminate\Support\Str::limit(str_replace("\n", ' / ', $r['note']), 40),
                ])
                ->all()
        );

        $overdue = count(array_filter($pending, fn ($r) => $r['overdue']));
        $withNote = count(array_filter($pending, fn ($r) => $r['note'] !== ''));
        $this->info(count($pending).' trucks resolved, '.$overdue.' already overdue, '.$withNote.' with a note from the sheet.');
        $this->reportSkips($skipped);

        if (! $write) {
            $this->newLine();
            $this->warn('Dry run: nothing was written. Re-run with --force to import.');

            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($pending as $row) {
                // Only the workshop's own note is stored; the due figures are
                // already columns on the list, so repeating them adds nothing.
                $note = $row['note'] !== '' ? $row['note'] : null;

                // One open row per truck, refreshed rather than duplicated.
                TruckServiceAlert::updateOrCreate(
                    [
                        'truck_number' => $row['truck_number'],
                        'resolved_at' => null,
                    ],
                    [
                        'current_km' => $row['current_km'],
                        'due_at_km' => $row['due_at_km'],
                        'km_source' => $row['km_source'],
                        'source' => 'mapon_reminder',
                        'note' => $note,
                        'flagged_at' => now(),
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        }

        $this->newLine();
        $this->info('Imported '.count($pending).' trucks.');

        return 0;
    }

    protected function isOilService(string $title): bool
    {
        foreach ($this->oilTitles as $needle) {
            if (str_contains($title, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function reportSkips(array $skipped): void
    {
        $this->newLine();
        $this->line('Skipped:');
        $this->line("  not an oil service      {$skipped['not_oil']}");
        $this->line("  already completed       {$skipped['completed']}");
        $this->line("  no remaining mileage    {$skipped['no_mileage']}");
        $this->line('  not found in Mapon      '.count($skipped['unmatched']));
        $this->line('  no odometer in Mapon    '.count($skipped['no_reading']));

        foreach (['unmatched' => 'Not found in Mapon', 'no_reading' => 'No odometer reading'] as $key => $label) {
            if (empty($skipped[$key])) {
                continue;
            }

            $this->newLine();
            $this->warn($label.':');

            foreach (array_keys($skipped[$key]) as $plate) {
                $this->line('  '.$plate);
            }
        }
    }
}
