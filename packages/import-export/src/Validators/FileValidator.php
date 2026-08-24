<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Validators;

use Illuminate\Http\UploadedFile;
use MageTech\ImportExport\Exceptions\FileValidationException;

final class FileValidator
{
    private array $errors = [];

    public function __construct(
        private ?int $maxFileSize = null,
        private ?array $allowedExtensions = null,
        private ?array $allowedMimeTypes = null,
        private bool $validateMimeReal = true,
        private bool $preventPathTraversal = true,
    ) {
        $this->maxFileSize ??= config('mts-import-export.upload.max_file_size', 10240) * 1024;
        $this->allowedExtensions ??= config('mts-import-export.upload.allowed_extensions', ['csv', 'xlsx', 'json', 'xml']);
        $this->allowedMimeTypes ??= config('mts-import-export.upload.allowed_mime_types', []);
    }

    public function validate(UploadedFile|string $file): bool
    {
        $this->errors = [];

        if (is_string($file)) {
            $this->validatePath($file);

            return $this->isValid();
        }

        $this->validateUploadedFile($file);

        return $this->isValid();
    }

    public function validateUploadedFile(UploadedFile $file): void
    {
        if ($this->preventPathTraversal) {
            $realPath = realpath($file->getPathname());

            if ($realPath === false || ! str_starts_with($realPath, sys_get_temp_dir())) {
                $this->errors[] = 'Invalid file path detected.';
            }
        }

        if (! $file->isValid()) {
            $this->errors[] = 'File upload failed.';
        }

        $this->validateSize($file->getSize());
        $this->validateExtension($file->getClientOriginalExtension());
        $this->validateMimeType($file->getClientMimeType(), $file->getPathname());
    }

    public function validatePath(string $path): void
    {
        if ($this->preventPathTraversal) {
            $realPath = realpath($path);

            if ($realPath === false) {
                $this->errors[] = 'File not found: '.$path;
            }
        }

        if (! file_exists($path)) {
            $this->errors[] = 'File not found: '.$path;

            return;
        }

        $this->validateSize((int) filesize($path));
        $this->validateExtension(pathinfo($path, PATHINFO_EXTENSION));

        if ($this->validateMimeReal) {
            $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            $this->validateMimeType($mimeType, $path);
        }
    }

    public function validateSize(int $size): void
    {
        if ($this->maxFileSize !== null && $size > $this->maxFileSize) {
            $maxMb = round($this->maxFileSize / (1024 * 1024), 2);
            $actualMb = round($size / (1024 * 1024), 2);
            $this->errors[] = "File size {$actualMb}MB exceeds maximum allowed size of {$maxMb}MB.";
        }
    }

    public function validateExtension(string $extension): void
    {
        $extension = strtolower($extension);

        if (in_array($extension, $this->allowedExtensions, true) === false) {
            $allowed = implode(', ', $this->allowedExtensions);
            $this->errors[] = "File extension '{$extension}' is not allowed. Allowed: {$allowed}.";
        }
    }

    public function validateMimeType(string $mimeType, string $path): void
    {
        if ($this->allowedMimeTypes !== [] && in_array($mimeType, $this->allowedMimeTypes, true) === false) {
            $this->errors[] = "MIME type '{$mimeType}' is not allowed.";
        }
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function throwIfInvalid(UploadedFile|string $file): void
    {
        $this->validate($file);

        if (! $this->isValid()) {
            throw FileValidationException::withErrors($this->errors);
        }
    }

    public static function getFileExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public static function detectFileType(string $path): ?string
    {
        $extension = self::getFileExtension($path);

        return match ($extension) {
            'csv' => 'csv',
            'xlsx', 'xls' => 'xlsx',
            'json' => 'json',
            'xml' => 'xml',
            default => null,
        };
    }
}
