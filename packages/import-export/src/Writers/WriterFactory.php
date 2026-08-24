<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Writers;

use MageTech\ImportExport\Validators\FileValidator;

final class WriterFactory
{
    public static function make(string $filePath): WriterInterface
    {
        $type = FileValidator::detectFileType($filePath);

        return match ($type) {
            'csv' => new CsvWriter,
            'xlsx' => new XlsxWriter,
            'json' => new JsonWriter,
            'xml' => new XmlWriter,
            default => throw new \InvalidArgumentException("Unsupported file type: {$type}"),
        };
    }

    /**
     * @return array<string, class-string<WriterInterface>>
     */
    public static function supported(): array
    {
        return [
            'csv' => CsvWriter::class,
            'xlsx' => XlsxWriter::class,
            'json' => JsonWriter::class,
            'xml' => XmlWriter::class,
        ];
    }

    public static function isSupported(string $filePath): bool
    {
        $type = FileValidator::detectFileType($filePath);

        return $type !== null && array_key_exists($type, self::supported());
    }
}
