<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Concerns;

use Illuminate\Database\Eloquent\Collection;
use MageTech\ImportExport\Models\Import;

/**
 * Add this trait to models that support importing.
 */
trait HasImport
{
    public function importRelations()
    {
        return $this->hasMany(Import::class, 'created_by');
    }

    /**
     * Get the latest import for this model.
     */
    public function latestImport(): ?Import
    {
        return $this->importRelations()
            ->latest()
            ->first();
    }

    /**
     * Get failed imports.
     *
     * @return Collection<int, Import>
     */
    public function failedImports()
    {
        return $this->importRelations()
            ->where('status', 'failed')
            ->get();
    }
}
