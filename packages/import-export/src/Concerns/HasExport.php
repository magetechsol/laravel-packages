<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Concerns;

use MageTech\ImportExport\Models\Export;

/**
 * Add this trait to models that support exporting.
 */
trait HasExport
{
    public function exportRelations()
    {
        return $this->hasMany(Export::class, 'created_by');
    }

    /**
     * Get the latest export for this model.
     */
    public function latestExport(): ?Export
    {
        return $this->exportRelations()
            ->latest()
            ->first();
    }
}
