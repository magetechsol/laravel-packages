<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use MageTech\Audit\Contracts\AuditIntegrityService;
use MageTech\Audit\Models\Audit;

class VerifyIntegrityCommand extends Command
{
    protected $signature = 'audit:verify-integrity
                    {--from= : Start date (Y-m-d)}
                    {--to= : End date (Y-m-d)}
                    {--batch-size=1000 : Number of records to process at once}';

    protected $description = 'Verify the integrity hash chain of audit records';

    public function handle(): int
    {
        if (!config('audit.integrity.enabled', false)) {
            $this->error('Integrity verification is not enabled. Enable it in config/audit.php.');

            return self::FAILURE;
        }

        $this->info('MTS Laravel Audit Pro - Integrity Verification');
        $this->newLine();

        $query = Audit::query();

        if ($from = $this->option('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $this->option('to')) {
            $query->where('created_at', '<=', $to);
        }

        $totalRecords = $query->count();
        $this->info("Total records to verify: {$totalRecords}");
        $this->newLine();

        $verified = 0;
        $invalid = 0;
        $missing = 0;
        $errors = [];

        $batchSize = (int) $this->option('batch-size');

        $query->orderBy('id')
            ->chunk($batchSize, function ($records) use (&$verified, &$invalid, &$missing, &$errors) {
                foreach ($records as $record) {
                    if ($record->record_hash === null) {
                        $missing++;
                        continue;
                    }

                    $integrityService = app(AuditIntegrityService::class);

                    $data = [
                        'event' => $record->event,
                        'auditable_type' => $record->auditable_type,
                        'auditable_id' => $record->auditable_id,
                        'actor_type' => $record->actor_type,
                        'actor_id' => $record->actor_id,
                        'action' => $record->action,
                        'old_values' => $record->old_values,
                        'new_values' => $record->new_values,
                        'tenant_id' => $record->tenant_id,
                        'created_at' => $record->created_at?->toDateTimeString(),
                    ];

                    if ($integrityService->verifyHash($data, $record->record_hash, $record->previous_hash)) {
                        $verified++;
                    } else {
                        $invalid++;
                        $errors[] = [
                            'id' => $record->id,
                            'uuid' => $record->uuid,
                            'created_at' => $record->created_at,
                        ];
                    }

                    $this->line("  Verified: {$verified} | Invalid: {$invalid} | Missing: {$missing}", 'info');
                }
            });

        $this->newLine();
        $this->info('Verification Results:');
        $this->line("  Verified records: {$verified}");
        $this->line("  Invalid records:  {$invalid}");
        $this->line("  Missing hashes:  {$missing}");

        if (!empty($errors)) {
            $this->newLine();
            $this->error('Invalid Records:');
            foreach (array_slice($errors, 0, 20) as $error) {
                $this->line("  ID: {$error['id']} | UUID: {$error['uuid']} | Date: {$error['created_at']}");
            }

            if (count($errors) > 20) {
                $this->line('  ... and ' . (count($errors) - 20) . ' more');
            }
        }

        $this->newLine();

        if ($invalid > 0) {
            $this->error('Integrity verification failed. Some records have been tampered with or corrupted.');
            $this->line('Note: Hash chaining provides tamper evidence, not absolute tamper prevention.');
        } else {
            $this->info('All records verified successfully.');
        }

        return $invalid > 0 ? self::FAILURE : self::SUCCESS;
    }
}
