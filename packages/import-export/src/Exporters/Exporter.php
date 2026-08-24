<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Exporters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use MageTech\ImportExport\Events\ExportCompleted;
use MageTech\ImportExport\Events\ExportFailed;
use MageTech\ImportExport\Events\ExportStarted;
use MageTech\ImportExport\Models\Export as ExportModel;
use MageTech\ImportExport\Writers\WriterFactory;

final class Exporter
{
    /** @var callable|null */
    private $filterCallback = null;

    public function __construct(
        private string $modelClass,
        private array $columns = [],
        private string $disk = 'local',
        private int $chunkSize = 1000,
    ) {
    }

    public function withFilter(?callable $filterCallback): static
    {
        $this->filterCallback = $filterCallback;

        return $this;
    }

    public function process(ExportModel $export): void
    {
        $export->markAsProcessing();
        Event::dispatch(new ExportStarted($export));

        try {
            $query = $this->buildQuery($export);
            $totalRows = $query->count();
            $export->update(['total_rows' => $totalRows]);

            $exportPath = $this->getExportPath($export);

            $exportDir = dirname($exportPath);
            if (! is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $writer = WriterFactory::make($exportPath);

            if ($this->columns !== []) {
                $writer->setHeaders($this->columns);
            }

            $writer->open($exportPath);

            $processed = 0;

            $query->chunkById($this->chunkSize, function ($rows) use ($writer, &$processed, $export) {
                foreach ($rows as $row) {
                    if ($row instanceof Model) {
                        $rowData = $this->columns !== []
                            ? $row->only($this->columns)
                            : $row->toArray();
                    } else {
                        $rowData = is_array($row)
                            ? ($this->columns !== [] ? array_intersect_key($row, array_flip($this->columns)) : $row)
                            : (array) $row;
                    }

                    $writer->write([(array) $rowData]);
                    $processed++;
                }

                $export->update(['processed_rows' => $processed]);
            });

            $writer->close();

            $export->update([
                'file_path' => $exportPath,
                'processed_rows' => $processed,
            ]);

            $export->markAsCompleted();
            Event::dispatch(new ExportCompleted($export));
        } catch (\Throwable $e) {
            $export->markAsFailed($e->getMessage());
            Event::dispatch(new ExportFailed($export, $e));

            throw $e;
        }
    }

    private function buildQuery(ExportModel $export): Builder
    {
        /** @var Builder $query */
        $query = $this->modelClass::query();

        if ($this->filterCallback !== null) {
            $query = ($this->filterCallback)($query);
        }

        if ($export->filters !== null) {
            foreach ($export->filters as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    private function getExportPath(ExportModel $export): string
    {
        $fileName = $export->file_name ?? ($export->name.'.'.$export->file_type);

        return storage_path('app/exports/'.$export->id.'_'.$fileName);
    }
}
