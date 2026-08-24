<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use MageTech\ImportExport\Export;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:export', description: 'Export a model to a file')]
class ExportCommand extends Command
{
    protected $signature = 'mts:export
        {model : The Eloquent model class to export}
        {--format=csv : The export format (csv, xlsx, json, xml)}
        {--disk=local : The filesystem disk}
        {--path= : The output file path}
        {--columns= : Comma-separated list of columns to export}
        {--queue : Queue the export instead of processing synchronously}';

    protected $description = 'Export a model to a file';

    public function handle(): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Model class '{$modelClass}' does not exist.");

            return Command::FAILURE;
        }

        $format = $this->option('format');
        $path = $this->option('path') ?? ($modelClass.'_export_'.now()->format('Y_m_d_His').".{$format}");

        $export = Export::make($modelClass)
            ->to($path)
            ->disk($this->option('disk'))
            ->format($format);

        $columns = $this->option('columns');

        if ($columns !== null) {
            $export->columns(explode(',', $columns));
        }

        $this->info("Starting export of {$modelClass}...");

        try {
            if ($this->option('queue')) {
                $result = $export->queue();
                $this->info("Export queued. ID: {$result->id}");
            } else {
                $result = $export->process();
                $this->info("Export completed. File: {$result->file_path}");
            }
        } catch (\Throwable $e) {
            $this->error("Export failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
