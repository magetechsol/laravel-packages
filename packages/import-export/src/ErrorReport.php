<?php

declare(strict_types=1);

namespace MageTech\ImportExport;

use MageTech\ImportExport\Models\Import;
use MageTech\ImportExport\Writers\WriterFactory;

final class ErrorReport
{
    public function generate(Import $import): ?string
    {
        $errors = $import->errors()->get();

        if ($errors->isEmpty()) {
            return null;
        }

        $format = config('mts-import-export.error_handling.error_report_format', 'csv');
        $disk = config('mts-import-export.disk', 'local');
        $fileName = "import_{$import->id}_errors_".now()->format('Y_m_d_His').".{$format}";
        $path = storage_path("app/error_reports/{$fileName}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = WriterFactory::make($path);
        $writer->setHeaders(['row_number', 'column', 'value', 'error', 'error_code']);
        $writer->open($path);

        $chunk = [];

        foreach ($errors as $error) {
            $chunk[] = [
                'row_number' => $error->row_number,
                'column' => $error->column ?? '',
                'value' => $error->value ?? '',
                'error' => $error->error,
                'error_code' => $error->error_code ?? '',
            ];

            if (count($chunk) >= 500) {
                $writer->write($chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $writer->write($chunk);
        }

        $writer->close();

        $import->update(['error_report_path' => $path]);

        return $path;
    }

    public function getDownloadUrl(Import $import): ?string
    {
        $reportPath = $import->error_report_path;

        if ($reportPath === null || ! file_exists($reportPath)) {
            return null;
        }

        return route('mts-import-export.error-report', $import->id);
    }

    public function getErrorsAsArray(Import $import): array
    {
        return $import->errors()
            ->select(['row_number', 'column', 'value', 'error', 'error_code'])
            ->orderBy('row_number')
            ->get()
            ->toArray();
    }
}
