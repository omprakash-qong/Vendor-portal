<?php

namespace App\Services\Catalogue;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExtractionService
{
    public function extract(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheets      = [];

        foreach ($spreadsheet->getAllSheets() as $worksheet) {
            $data = $this->extractSheet($worksheet);
            if (!empty($data['rows'])) {
                $sheets[] = $data;
            }
        }

        return ['sheets' => $sheets, 'sheet_count' => count($sheets)];
    }

    private function extractSheet(Worksheet $sheet): array
    {
        $rows    = [];
        $headers = [];
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestCol; $col++) {
                $cell  = $sheet->getCell($col . $row);
                $value = $cell->getFormattedValue();
                $rowData[$col] = trim((string) $value);
            }

            // Skip entirely empty rows
            if (count(array_filter($rowData)) === 0) continue;

            // First non-empty row = headers
            if (empty($headers)) {
                $headers = array_values($rowData);
                continue;
            }

            // Map values to headers
            $mapped = [];
            $colKeys = array_keys($rowData);
            foreach ($colKeys as $idx => $col) {
                $header = $headers[$idx] ?? "col_$col";
                if ($header !== '') {
                    $mapped[$header] = $rowData[$col];
                }
            }

            if (count(array_filter($mapped)) > 0) {
                $rows[] = $mapped;
            }
        }

        return [
            'sheet_name' => $sheet->getTitle(),
            'headers'    => $headers,
            'rows'       => $rows,
            'row_count'  => count($rows),
        ];
    }
}
