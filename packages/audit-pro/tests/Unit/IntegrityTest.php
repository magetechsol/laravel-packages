<?php

declare(strict_types=1);

use MageTech\Audit\Tests\TestCase;
use MageTech\Audit\Services\AuditIntegrityService;

class IntegrityTest extends TestCase
{
    public function test_hash_generation(): void
    {
        $this->app['config']->set('audit.integrity.enabled', true);
        $this->app['config']->set('audit.integrity.algorithm', 'sha256');

        $service = new AuditIntegrityService();

        $data = [
            'event' => 'created',
            'auditable_type' => 'App\\Models\\Order',
            'auditable_id' => 1,
            'actor_type' => 'App\\Models\\User',
            'actor_id' => 1,
        ];

        $hash = $service->generateHash($data);

        $this->assertNotEmpty($hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function test_hash_verification(): void
    {
        $service = new AuditIntegrityService();

        $data = [
            'event' => 'created',
            'auditable_type' => 'App\\Models\\Order',
            'auditable_id' => 1,
        ];

        $hash = $service->generateHash($data);

        $this->assertTrue($service->verifyHash($data, $hash));
    }

    public function test_hash_verification_fails_with_tampered_data(): void
    {
        $service = new AuditIntegrityService();

        $data = [
            'event' => 'created',
            'auditable_type' => 'App\\Models\\Order',
            'auditable_id' => 1,
        ];

        $hash = $service->generateHash($data);

        $tamperedData = $data;
        $tamperedData['auditable_id'] = 2;

        $this->assertFalse($service->verifyHash($tamperedData, $hash));
    }

    public function test_hash_chain(): void
    {
        $service = new AuditIntegrityService();

        $data1 = ['event' => 'created', 'auditable_id' => 1];
        $data2 = ['event' => 'updated', 'auditable_id' => 1];

        $hash1 = $service->generateHash($data1);
        $hash2 = $service->generateHash($data2, $hash1);

        $this->assertNotEmpty($hash1);
        $this->assertNotEmpty($hash2);
        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_algorithm_configurable(): void
    {
        $this->app['config']->set('audit.integrity.algorithm', 'sha512');

        $service = new AuditIntegrityService();

        $this->assertEquals('sha512', $service->getAlgorithm());
    }
}
