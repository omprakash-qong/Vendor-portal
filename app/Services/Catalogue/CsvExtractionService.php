<?php

namespace App\Services\Catalogue;

use League\Csv\Reader;

class CsvExtractionService
{
    public function extract(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $rows    = [];

        foreach ($csv->getRecords() as $record) {
            // Skip fully empty rows
            if (count(array_filter(array_map('trim', $record))) === 0) continue;
            $rows[] = $record;
        }

        return [
            'sheets' => [[
                'sheet_name' => 'CSV Data',
                'headers'    => $headers,
                'rows'       => $rows,
                'row_count'  => count($rows),
            ]],
            'sheet_count' => 1,
        ];
    }
}
