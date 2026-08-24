<?php

declare(strict_types=1);

namespace MageTech\ImportExport;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use MageTech\ImportExport\Enums\ImportStatus;
use MageTech\ImportExport\Exceptions\FileValidationException;
use MageTech\ImportExport\Exceptions\ImportException;
use MageTech\ImportExport\Importers\Importer;
use MageTech\ImportExport\Jobs\ProcessImportJob;
use MageTech\ImportExport\Mappers\ColumnMapper;
use MageTech\ImportExport\Models\Import as ImportModel;
use MageTech\ImportExport\Transformers\TransformerPipeline;
use MageTech\ImportExport\Validators\DuplicateDetector;
use MageTech\ImportExport\Validators\FileValidator;
use MageTech\ImportExport\Validators\RowValidator;

final class Import
{
    private string $modelClass;

    private mixed $file = null;

    private string $disk = 'local';

    private array $mapping = [];

    private array $defaults = [];

    private array $skipColumns = [];

    private array $validationRules = [];

    private array $transformTypes = [];

    /** @var callable|null */
    private $transformCallback = null;

    private string $duplicateMode = 'ignore';

    private ?string $duplicateKey = null;

    private ?int $chunkSize = null;

    private ?string $queueConnection = null;

    private ?string $queueName = null;

    private ?int $timeout = null;

    private array $options = [];

    private ?ImportModel $importModel = null;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->chunkSize = config('mts-import-export.import.chunk_size', 1000);
        $this->queueConnection = config('mts-import-export.import.queue_connection', 'redis');
        $this->queueName = config('mts-import-export.import.queue_name', 'imports');
        $this->timeout = config('mts-import-export.import.timeout', 600);
    }

    public static function make(string $modelClass): static
    {
        return new self($modelClass);
    }

    public function from(UploadedFile|string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function map(array $mapping): static
    {
        $this->mapping = $mapping;

        return $this;
    }

    public function defaults(array $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function skipColumns(array $columns): static
    {
        $this->skipColumns = $columns;

        return $this;
    }

    public function validate(array $rules): static
    {
        $this->validationRules = $rules;

        return $this;
    }

    public function transformTypes(array $types): static
    {
        $this->transformTypes = $types;

        return $this;
    }

    public function transform(callable $callback): static
    {
        $this->transformCallback = $callback;

        return $this;
    }

    public function duplicateDetection(string $mode, ?string $key = null): static
    {
        $this->duplicateMode = $mode;
        $this->duplicateKey = $key;

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
     * Process the import synchronously.
     */
    public function process(): ImportModel
    {
        $import = $this->createImportRecord();
        $importer = $this->buildImporter();

        $importer->process($import);

        return $import->fresh() ?? $import;
    }

    /**
     * Queue the import for async processing.
     */
    public function queue(): ImportModel
    {
        $import = $this->createImportRecord();
        $import->update(['status' => ImportStatus::Queued]);

        ProcessImportJob::dispatch($import)
            ->onConnection($this->queueConnection)
            ->onQueue($this->queueName);

        return $import;
    }

    /**
     * Create the import record in the database.
     */
    private function createImportRecord(): ImportModel
    {
        $filePath = $this->resolveFilePath();
        $fileType = FileValidator::detectFileType($filePath);

        if ($fileType === null) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            throw FileValidationException::withErrors([
                "File extension '{$ext}' is not allowed. Allowed: csv, xlsx, json, xml.",
            ]);
        }

        return DB::transaction(function () use ($filePath, $fileType) {
            $importOptions = array_merge($this->options, [
                'model_class' => $this->modelClass,
                'validation_rules' => $this->validationRules,
                'transform_types' => $this->transformTypes,
                'duplicate_mode' => $this->duplicateMode,
                'duplicate_key' => $this->duplicateKey,
                'defaults' => $this->defaults,
                'skip_columns' => $this->skipColumns,
                'chunk_size' => $this->chunkSize,
            ]);

            $import = ImportModel::create([
                'name' => $this->options['name'] ?? pathinfo($this->getFileName(), PATHINFO_FILENAME),
                'file_path' => $filePath,
                'file_name' => $this->getFileName(),
                'file_type' => $fileType,
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0,
                'status' => ImportStatus::Pending,
                'options' => $importOptions,
                'mapping' => $this->mapping !== [] ? $this->mapping : null,
                'created_by' => $this->options['created_by'] ?? null,
            ]);

            $this->importModel = $import;

            return $import;
        });
    }

    private function buildImporter(): Importer
    {
        $mapper = new ColumnMapper(
            mapping: $this->mapping,
            defaults: $this->defaults,
            skipColumns: $this->skipColumns,
        );

        $rowValidator = new RowValidator(rules: $this->validationRules);

        $duplicateDetector = new DuplicateDetector(
            mode: $this->duplicateMode,
            uniqueKey: $this->duplicateKey,
            modelClass: $this->modelClass,
        );

        $transformerPipeline = new TransformerPipeline;

        if (config('mts-import-export.security.formula_injection_protection', true)) {
            $transformerPipeline->withSanitizer();
        }

        if ($this->transformTypes !== []) {
            $transformerPipeline->withTypeCaster($this->transformTypes);
        }

        if ($this->transformCallback !== null) {
            $transformerPipeline->add($this->transformCallback);
        }

        $fileValidator = new FileValidator;

        return new Importer(
            modelClass: $this->modelClass,
            mapper: $mapper,
            rowValidator: $rowValidator,
            duplicateDetector: $duplicateDetector,
            transformerPipeline: $transformerPipeline,
            fileValidator: $fileValidator,
            chunkSize: $this->chunkSize,
        );
    }

    private function resolveFilePath(): string
    {
        if ($this->file instanceof UploadedFile) {
            return $this->file->getRealPath();
        }

        if (is_string($this->file)) {
            if (str_starts_with($this->file, 'http://') || str_starts_with($this->file, 'https://')) {
                return $this->file;
            }

            return $this->file;
        }

        throw ImportException::invalidFile('No file provided.');
    }

    private function getFileName(): string
    {
        if ($this->file instanceof UploadedFile) {
            return $this->file->getClientOriginalName();
        }

        if (is_string($this->file)) {
            return basename($this->file);
        }

        return 'unknown';
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getImportModel(): ?ImportModel
    {
        return $this->importModel;
    }
}
