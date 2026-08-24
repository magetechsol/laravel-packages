<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exceptions;

use RuntimeException;

class ImportCancelledException extends RuntimeException
{
    public function __construct(int $importId)
    {
        parent::__construct("Import #{$importId} was cancelled.");
    }
}
