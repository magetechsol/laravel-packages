<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class ExportException extends RuntimeException
{
    public static function cannotOpenFile(string $path): static
    {
        return new static("Cannot open file for writing: {$path}");
    }

    public static function exportFailed(int $exportId, string $reason = ''): static
    {
        $message = "Export #{$exportId} failed";

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new static($message);
    }

    public static function noDataToExport(): static
    {
        return new static('No data to export.');
    }

    public static function unsupportedFileType(string $type): static
    {
        return new static("Unsupported file type: {$type}");
    }
}
