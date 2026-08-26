<?php

declare(strict_types=1);

use MageTech\Audit\Tests\TestCase;
use MageTech\Audit\Tests\Models\TestOrder;
use MageTech\Audit\Models\Audit;
use MageTech\Audit\Facades\Audit as AuditFacade;

class ModelAuditingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create('orders', function ($table) {
            $table->id();
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    public function test_model_creation_creates_audit(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $this->assertDatabaseHas('audits', [
            'event' => 'created',
            'auditable_type' => TestOrder::class,
            'auditable_id' => $order->id,
        ]);
    }

    public function test_model_update_creates_audit_with_old_and_new_values(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        Audit::flushMacroAssertions();

        $order->update([
            'status' => 'approved',
            'amount' => 150.00,
        ]);

        $audit = Audit::where('event', 'updated')
            ->where('auditable_type', TestOrder::class)
            ->where('auditable_id', $order->id)
            ->latest()
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('pending', $audit->old_values['status']);
        $this->assertEquals('approved', $audit->new_values['status']);
        $this->assertEquals(100.00, $audit->old_values['amount']);
        $this->assertEquals(150.00, $audit->new_values['amount']);
    }

    public function test_model_deletion_creates_audit(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $orderId = $order->id;
        $order->delete();

        $this->assertDatabaseHas('audits', [
            'event' => 'deleted',
            'auditable_type' => TestOrder::class,
            'auditable_id' => $orderId,
        ]);
    }

    public function test_model_audits_relationship(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $this->assertCount(1, $order->audits);
    }

    public function test_changed_values_computed_correctly(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $order->update(['status' => 'approved']);

        $audit = Audit::where('event', 'updated')
            ->where('auditable_type', TestOrder::class)
            ->where('auditable_id', $order->id)
            ->latest()
            ->first();

        $this->assertNotNull($audit->changed_values);
        $this->assertEquals('pending', $audit->changed_values['status']['old']);
        $this->assertEquals('approved', $audit->changed_values['status']['new']);
    }

    public function test_audit_metadata_attached(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $audit = Audit::where('event', 'created')
            ->where('auditable_type', TestOrder::class)
            ->where('auditable_id', $order->id)
            ->first();

        $this->assertEquals('test', $audit->metadata['source'] ?? null);
    }

    public function test_audit_tags_attached(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $audit = Audit::where('event', 'created')
            ->where('auditable_type', TestOrder::class)
            ->where('auditable_id', $order->id)
            ->first();

        $this->assertContains('order', $audit->tags);
    }
}
