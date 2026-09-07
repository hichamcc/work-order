<?php

namespace App\Services;

use RuntimeException;

/**
 * Minimal xlsx/csv reader.
 *
 * An xlsx is a zip of XML, so the sheet can be read with the extensions PHP
 * already ships with rather than pulling in a spreadsheet package for what is
 * a one-off import.
 */
class SpreadsheetReader
{
    /**
     * Read a sheet into rows keyed by their header names.
     *
     * @return array<int, array<string, string>>
     */
    public function rows(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Cannot read file: {$path}");
        }

        $raw = str_ends_with(strtolower($path), '.csv')
            ? $this->readCsv($path)
            : $this->readXlsx($path);

        if (empty($raw)) {
            return [];
        }

        $headers = array_map(fn ($h) => trim($h), array_shift($raw));
        $rows = [];

        foreach ($raw as $cells) {
            $row = [];

            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = trim($cells[$i] ?? '');
            }

            // Skip rows that are entirely blank.
            if (implode('', $row) !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        while (($cells = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($c) => (string) $c, $cells);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Cannot open xlsx: {$path}");
        }

        $shared = $this->sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('No sheet found in workbook.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $c) {
                $index = $this->columnIndex((string) $c['r']);
                $type = (string) $c['t'];
                $value = isset($c->v) ? (string) $c->v : '';

                if ($type === 's') {
                    $value = $shared[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $c->is->t;
                }

                $cells[$index] = $value;
            }

            if (empty($cells)) {
                $rows[] = [];
                continue;
            }

            // Fill gaps so every row lines up with the header positions.
            $rows[] = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    protected function sharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $parsed = simplexml_load_string($xml);
        $strings = [];

        foreach ($parsed->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            // Rich text is split across runs; join them back together.
            $text = '';

            foreach ($si->r ?? [] as $run) {
                $text .= (string) $run->t;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * "C12" is the third column, so zero-indexed 2.
     */
    protected function columnIndex(string $reference): int
    {
        preg_match('/^([A-Z]+)/', $reference, $matches);

        $letters = $matches[1] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - ord('A') + 1);
        }

        return $index - 1;
    }
}
