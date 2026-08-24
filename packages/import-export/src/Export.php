<?php

declare(strict_types=1);

namespace MageTech\ImportExport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use MageTech\ImportExport\Exporters\Exporter;
use MageTech\ImportExport\Jobs\ExportJob;
use MageTech\ImportExport\Models\Export as ExportModel;

final class Export
{
    private string $modelClass;

    private ?string $fileType = null;

    private string $disk = 'local';

    private ?string $filePath = null;

    private ?string $fileName = null;

    private array $columns = [];

    /** @var callable|null */
    private $filterCallback = null;

    private ?int $chunkSize = null;

    private ?string $queueConnection = null;

    private ?string $queueName = null;

    private ?int $timeout = null;

    private array $options = [];

    private ?ExportModel $exportModel = null;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->chunkSize = config('mts-import-export.export.chunk_size', 1000);
        $this->queueConnection = config('mts-import-export.export.queue_connection', 'redis');
        $this->queueName = config('mts-import-export.export.queue_name', 'exports');
        $this->timeout = config('mts-import-export.export.timeout', 600);
    }

    public static function make(string $modelClass): static
    {
        return new self($modelClass);
    }

    public function to(string $fileName): static
    {
        $this->fileName = $fileName;
        $this->fileType = $this->detectFileType($fileName);

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function format(string $type): static
    {
        $this->fileType = $type;

        return $this;
    }

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function filter(callable $callback): static
    {
        $this->filterCallback = $callback;

        return $this;
    }

    public function chunkSize(int $size): static
    {
        $this->chunkSize = $size;

        return $this;
    }

    public function onConnection(string $connection): static
    {
        $this->queueConnection = $connection;

        return $this;
    }

    public function onQueue(string $queue): static
    {
        $this->queueName = $queue;

        return $this;
    }

    public function withTimeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function withOptions(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Process the export synchronously.
     */
    public function process(): ExportModel
    {
        $export = $this->createExportRecord();
        $exporter = $this->buildExporter();

        $exporter->process($export);

        return $export->fresh() ?? $export;
    }

    /**
     * Queue the export for async processing.
     */
    public function queue(): ExportModel
    {
        $export = $this->createExportRecord();

        $job = (new ExportJob($export))
            ->onConnection($this->queueConnection)
            ->onQueue($this->queueName);

        if ($this->timeout !== null) {
            $job->timeout = $this->timeout;
        }

        dispatch($job);

        return $export;
    }

    /**
     * Build a query for the export.
     */
    public function buildQuery(): Builder
    {
        /** @var Builder $query */
        $query = $this->modelClass::query();

        if ($this->filterCallback !== null) {
            $filterFn = $this->filterCallback;
            $query = $filterFn($query);
        }

        return $query;
    }

    private function createExportRecord(): ExportModel
    {
        $fileType = $this->fileType ?? 'csv';
        $fileName = $this->fileName ?? ($this->modelClass::class.'_export_'.now()->format('Y_m_d_His').'.'.$fileType);

        return DB::transaction(function () use ($fileType, $fileName) {
            $export = ExportModel::create([
                'name' => $this->options['name'] ?? pathinfo($fileName, PATHINFO_FILENAME),
                'model_class' => $this->modelClass,
                'file_type' => $fileType,
                'file_name' => $fileName,
                'status' => 'pending',
                'columns' => $this->columns !== [] ? $this->columns : null,
                'filters' => $this->options['filters'] ?? null,
                'options' => $this->options,
                'created_by' => $this->options['created_by'] ?? null,
            ]);

            $this->exportModel = $export;

            return $export;
        });
    }

    private function buildExporter(): Exporter
    {
        $exporter = new Exporter(
            modelClass: $this->modelClass,
            columns: $this->columns,
            disk: $this->disk,
            chunkSize: $this->chunkSize,
        );

        if ($this->filterCallback !== null) {
            $exporter->withFilter($this->filterCallback);
        }

        return $exporter;
    }

    private function detectFileType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => 'csv',
            'xlsx', 'xls' => 'xlsx',
            'json' => 'json',
            'xml' => 'xml',
            default => 'csv',
        };
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getExportModel(): ?ExportModel
    {
        return $this->exportModel;
    }
}
