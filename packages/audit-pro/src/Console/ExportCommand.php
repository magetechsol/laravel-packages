<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use MageTech\Audit\Models\Audit;

class ExportCommand extends Command
{
    protected $signature = 'audit:export
                    {--format=csv : Export format (csv, json)}
                    {--from= : Start date (Y-m-d)}
                    {--to= : End date (Y-m-d)}
                    {--event= : Filter by event type}
                    {--actor= : Filter by actor type}
                    {--model= : Filter by auditable model}
                    {--tenant= : Filter by tenant ID}
                    {--output= : Output file path}
                    {--disk=local : Storage disk for output file}';

    protected $description = 'Export audit records to CSV or JSON';

    public function handle(): int
    {
        $this->info('MTS Laravel Audit Pro - Export');
        $this->newLine();

        $query = Audit::query();

        if ($from = $this->option('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $this->option('to')) {
            $query->where('created_at', '<=', $to);
        }

        if ($event = $this->option('event')) {
            $query->where('event', $event);
        }

        if ($actor = $this->option('actor')) {
            $query->where('actor_type', $actor);
        }

        if ($model = $this->option('model')) {
            $query->where('auditable_type', $model);
        }

        if ($tenant = $this->option('tenant')) {
            $query->where('tenant_id', $tenant);
        }

        $format = $this->option('format');
        $count = $query->count();

        $this->info("Found {$count} records to export.");

        if ($count === 0) {
            $this->warn('No records found matching the criteria.');

            return self::SUCCESS;
        }

        $filename = 'audit-export-' . date('Y-m-d-His') . ".{$format}";

        $columns = [
            'id', 'uuid', 'event', 'auditable_type', 'auditable_id',
            'actor_type', 'actor_id', 'actor_name', 'actor_email',
            'action', 'description', 'url', 'method', 'route',
            'ip_address', 'user_agent', 'request_id', 'session_id',
            'old_values', 'new_values', 'changed_values',
            'metadata', 'tags', 'tenant_id', 'batch_uuid',
            'created_at',
        ];

        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $exportData = [];

        $query->orderBy('id')->chunk(1000, function ($records) use (&$exportData, $columns, $progress) {
            foreach ($records as $record) {
                $row = [];

                foreach ($columns as $column) {
                    $value = $record->{$column};

                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $row[$column] = $value;
                }

                $exportData[] = $row;
                $progress->advance();
            }
        });

        $progress->finish();
        $this->newLine(2);

        $outputPath = $this->option('output');
        $disk = $this->option('disk');

        if ($format === 'csv') {
            $content = $this->toCsv($exportData, $columns);
        } else {
            $content = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        if ($outputPath) {
            file_put_contents($outputPath, $content);
            $this->info("Exported to: {$outputPath}");
        } else {
            $path = "audit-exports/{$filename}";
            Storage::disk($disk)->put($path, $content);
            $this->info("Exported to: storage/{$disk}/{$path}");
        }

        $this->newLine();
        $this->info("Export complete! {$count} records exported.");

        return self::SUCCESS;
    }

    protected function toCsv(array $data, array $columns): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $columns);

        foreach ($data as $row) {
            $csvRow = [];

            foreach ($columns as $column) {
                $csvRow[] = $row[$column] ?? '';
            }

            fputcsv($handle, $csvRow);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
