<?php

namespace App\Services\Catalogue;

use Illuminate\Http\UploadedFile;

class FileValidationService
{
    const ALLOWED_MIMES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
        'application/vnd.ms-excel'                                           => 'excel',
        'text/csv'                                                           => 'csv',
        'text/plain'                                                         => 'csv',
        'application/csv'                                                    => 'csv',
        'application/pdf'                                                    => 'pdf',
    ];

    const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20 MB

    public function validate(UploadedFile $file): array
    {
        $mime = $file->getMimeType();
        $ext  = strtolower($file->getClientOriginalExtension());

        // Size check
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->reject('File size exceeds 20 MB limit.');
        }

        // Extension-based type detection (MIME can be unreliable for CSV)
        $type = $this->detectType($mime, $ext);

        if (!$type) {
            return $this->reject(
                'Unsupported file type. Please upload a Digital PDF, Excel (.xlsx), or CSV file.'
            );
        }

        // PDF-specific validation — basic sanity check only
        if ($type === 'pdf') {
            $pdfCheck = $this->validateDigitalPdf($file->getRealPath());
            if (!$pdfCheck['valid']) {
                return $this->reject($pdfCheck['message']);
            }
        }

        return ['valid' => true, 'type' => $type, 'message' => null];
    }

    private function detectType(string $mime, string $ext): ?string
    {
        if ($ext === 'xlsx' || $ext === 'xls') return 'excel';
        if ($ext === 'csv') return 'csv';
        if ($ext === 'pdf') return 'pdf';

        return self::ALLOWED_MIMES[$mime] ?? null;
    }

    private function validateDigitalPdf(string $path): array
    {
        // Just check the file is readable and non-zero; text extraction happens in the background job
        if (!is_readable($path) || filesize($path) === 0) {
            return ['valid' => false, 'message' => 'The PDF file appears to be empty or unreadable.'];
        }

        // Confirm it starts with the PDF magic bytes
        $handle = fopen($path, 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            return ['valid' => false, 'message' => 'The file does not appear to be a valid PDF.'];
        }

        return ['valid' => true, 'message' => null];
    }

    private function reject(string $message): array
    {
        return ['valid' => false, 'type' => null, 'message' => $message];
    }
}
