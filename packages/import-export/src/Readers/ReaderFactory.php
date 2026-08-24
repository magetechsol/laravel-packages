<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Readers;

use MageTech\ImportExport\Validators\FileValidator;

final class ReaderFactory
{
    public static function make(string $filePath): ReaderInterface
    {
        $type = FileValidator::detectFileType($filePath);

        return match ($type) {
            'csv' => new CsvReader,
            'xlsx' => new XlsxReader,
            'json' => new JsonReader,
            'xml' => new XmlFileReader,
            default => throw new \InvalidArgumentException("Unsupported file type: {$type}"),
        };
    }

    /**
     * @return array<string, class-string<ReaderInterface>>
     */
    public static function supported(): array
    {
        return [
            'csv' => CsvReader::class,
            'xlsx' => XlsxReader::class,
            'json' => JsonReader::class,
            'xml' => XmlFileReader::class,
        ];
    }

    public static function isSupported(string $filePath): bool
    {
        $type = FileValidator::detectFileType($filePath);

        return $type !== null && array_key_exists($type, self::supported());
    }
}
