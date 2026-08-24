<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class ImportException extends RuntimeException
{
    public static function fileNotFound(string $path): static
    {
        return new static("File not found: {$path}");
    }

    public static function cannotOpenFile(string $path): static
    {
        return new static("Cannot open file: {$path}");
    }

    public static function invalidJson(string $path): static
    {
        return new static("Invalid JSON content in file: {$path}");
    }

    public static function invalidFile(string $message): static
    {
        return new static("Invalid file: {$message}");
    }

    public static function importFailed(int $importId, string $reason = ''): static
    {
        $message = "Import #{$importId} failed";

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new static($message);
    }

    public static function cancelled(int $importId): static
    {
        return new static("Import #{$importId} was cancelled.");
    }

    public static function alreadyProcessing(int $importId): static
    {
        return new static("Import #{$importId} is already being processed.");
    }

    public static function noRowsToProcess(): static
    {
        return new static('No rows to process.');
    }

    public static function unsupportedFileType(string $type): static
    {
        return new static("Unsupported file type: {$type}");
    }
}
