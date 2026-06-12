<?php

namespace App\Services\Catalogue;

use Illuminate\Support\Facades\Log;

class PdfExtractionService
{
    private string $scriptPath;

    public function __construct()
    {
        $this->scriptPath = base_path('scripts/extract_pdf.py');
    }

    public function extract(string $filePath): array
    {
        $python = $this->findPython();

        $cmd    = escapeshellcmd($python) . ' ' . escapeshellarg($this->scriptPath) . ' ' . escapeshellarg($filePath);
        $output = shell_exec($cmd . ' 2>&1');

        if (!$output) {
            throw new \RuntimeException('PDF extractor returned no output. Python may not be available.');
        }

        $data = json_decode($output, true);

        if (!is_array($data)) {
            throw new \RuntimeException('PDF extractor returned invalid JSON: ' . substr($output, 0, 300));
        }

        if (isset($data['error'])) {
            if ($data['error'] === 'scanned_pdf') {
                throw new \RuntimeException($data['message']);
            }
            throw new \RuntimeException('PDF extraction error: ' . $data['error']);
        }

        Log::info('PDF extracted', [
            'pages'    => $data['page_count']      ?? 0,
            'tables'   => count($data['tables']    ?? []),
            'variants' => $data['total_variants']  ?? 0,
            'category' => $data['detected_category'] ?? 'Unknown',
        ]);

        return $data;
    }

    private function findPython(): string
    {
        foreach (['python3', 'python', '/usr/bin/python3'] as $candidate) {
            if (shell_exec('which ' . escapeshellarg($candidate) . ' 2>/dev/null')) {
                return $candidate;
            }
        }
        throw new \RuntimeException('Python 3 is not available on this server.');
    }
}
