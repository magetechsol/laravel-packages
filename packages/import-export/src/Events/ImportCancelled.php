<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\ImportExport\Models\Import;

class ImportCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Import $import,
    ) {
    }
}
