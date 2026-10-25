<?php

namespace App\Console\Commands;

use App\Models\Trailer;
use App\Services\SpreadsheetReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTrailers extends Command
{
    protected $signature = 'trailers:import
                            {file : CSV or xlsx exported from WTTsystem}
                            {--column= : Header holding the plate number; detected when omitted}
                            {--force : Write the rows. Without it the command only reports}
                            {--deactivate-missing : Mark trailers absent from the file as inactive}';

    protected $description = 'Import the trailer list exported from WTTsystem';

    /**
     * Headers the plate number is usually found under.
     */
    protected array $plateHeaders = ['plate number', 'plate_number', 'plate', 'trailer', 'trailer number', 'number'];

    public function handle(SpreadsheetReader $reader)
    {
        $path = $this->argument('file');

        if (! is_readable($path)) {
            $this->error("File not found: {$path}");

            return 1;
        }

        $rows = $reader->rows($path);

        if (empty($rows)) {
            $this->error('The file has no rows.');

            return 1;
        }

        $column = $this->option('column') ?: $this->detectColumn($rows[0]);

        if (! $column) {
            $this->error('Could not find a plate number column. Headers seen: '.implode(', ', array_keys($rows[0])));
            $this->line('Pass one with --column="Plate Number".');

            return 1;
        }

        $this->info(count($rows).' rows read, using column "'.$column.'".');

        $seen = [];
        $merged = [];
        $noPlate = [];
        $blank = 0;

        foreach ($rows as $row) {
            $text = trim($row[$column] ?? '');

            if ($text === '') {
                $blank++;
                continue;
            }

            $key = Trailer::keyFor($text);

            if (! Trailer::extractPlate($text)) {
                $noPlate[] = $text;
            }

            // The same trailer is written several ways: "FJ4743", "Ernø FJ 4743"
            // and "FJ 47 43 DFDS" all land on one key. The first spelling seen
            // becomes the label, and the rest are reported as merged.
            if (isset($seen[$key])) {
                $merged[$key][] = $text;
                continue;
            }

            $seen[$key] = $text;
        }

        $new = [];
        $existing = [];

        foreach ($seen as $key => $label) {
            Trailer::where('plate_number', $key)->exists()
                ? $existing[] = $key
                : $new[] = $key;
        }

        $this->newLine();
        $this->line('  rows read           '.count($rows));
        $this->line('  distinct trailers   '.count($seen));
        $this->line('  new                 '.count($new));
        $this->line('  already known       '.count($existing));
        $this->line('  merged duplicates   '.array_sum(array_map('count', $merged)));
        $this->line('  blank rows          '.$blank);
        $this->line('  no plate found      '.count($noPlate).' (kept under their own text)');

        if (! empty($merged)) {
            $this->newLine();
            $this->warn('Rows merged onto an existing trailer:');

            foreach (array_slice($merged, 0, 12, true) as $key => $variants) {
                $this->line('  '.str_pad($key, 10).' <- '.$seen[$key].' + '.implode(' + ', $variants));
            }

            if (count($merged) > 12) {
                $this->line('  ... and '.(count($merged) - 12).' more');
            }
        }

        if (! empty($noPlate)) {
            $this->newLine();
            $this->warn('No registration pattern found in:');

            foreach ($noPlate as $text) {
                $this->line('  '.$text);
            }
        }

        $missing = collect();

        if ($this->option('deactivate-missing')) {
            $missing = Trailer::active()
                ->get()
                ->filter(fn (Trailer $trailer) => ! isset($seen[$trailer->plate_number]));

            $this->line('  not in this file    '.$missing->count().' (would be deactivated)');
        }

        if (! $this->option('force')) {
            $this->newLine();
            $this->warn('Dry run: nothing was written. Re-run with --force to import.');

            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($seen as $key => $label) {
                // Reactivates a trailer that is back in the list, and leaves any
                // service flag on an existing one untouched.
                Trailer::updateOrCreate(
                    ['plate_number' => $key],
                    ['label' => $label, 'is_active' => true]
                );
            }

            foreach ($missing as $trailer) {
                $trailer->update(['is_active' => false]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        }

        $this->newLine();
        $this->info('Imported. '.Trailer::active()->count().' active trailer(s).');

        return 0;
    }

    protected function detectColumn(array $firstRow): ?string
    {
        foreach (array_keys($firstRow) as $header) {
            if (in_array(strtolower(trim($header)), $this->plateHeaders, true)) {
                return $header;
            }
        }

        return null;
    }
}
