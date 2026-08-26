<?php

declare(strict_types=1);

use MageTech\Audit\Tests\TestCase;
use MageTech\Audit\Tests\Models\TestOrder;
use MageTech\Audit\Models\Audit;
use MageTech\Audit\Facades\Audit as AuditFacade;

class ManualAuditTest extends TestCase
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

    public function test_manual_audit_record(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        AuditFacade::record()
            ->event('invoice.approved')
            ->on($order)
            ->withMetadata([
                'source' => 'admin-panel',
            ])
            ->withTags(['invoice'])
            ->description('Invoice approved by admin')
            ->save();

        $this->assertDatabaseHas('audits', [
            'event' => 'invoice.approved',
            'auditable_type' => TestOrder::class,
            'auditable_id' => $order->id,
            'description' => 'Invoice approved by admin',
        ]);

        $audit = Audit::where('event', 'invoice.approved')
            ->where('auditable_id', $order->id)
            ->first();

        $this->assertEquals('admin-panel', $audit->metadata['source']);
        $this->assertContains('invoice', $audit->tags);
    }

    public function test_manual_event_with_actor(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        AuditFacade::event('payment.refunded')
            ->on($order)
            ->by([
                'type' => 'App\\Models\\User',
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ])
            ->metadata([
                'gateway' => 'stripe',
                'amount' => 50.00,
            ])
            ->save();

        $this->assertDatabaseHas('audits', [
            'event' => 'payment.refunded',
            'actor_type' => 'App\\Models\\User',
            'actor_id' => 1,
            'actor_name' => 'John Doe',
            'actor_email' => 'john@example.com',
        ]);
    }

    public function test_batch_operations(): void
    {
        $batchUuid = AuditFacade::beginBatch();

        $this->assertNotEmpty($batchUuid);

        for ($i = 0; $i < 5; $i++) {
            $order = TestOrder::create([
                'status' => 'pending',
                'amount' => 100.00 + $i,
            ]);

            AuditFacade::record()
                ->event('created')
                ->on($order)
                ->save();
        }

        AuditFacade::endBatch();

        $audits = Audit::where('batch_uuid', $batchUuid)->get();

        $this->assertCount(5, $audits);
    }

    public function test_audit_query_api(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $query = AuditFacade::query()
            ->whereEvent('created')
            ->whereModel(TestOrder::class, $order->id)
            ->latest();

        $this->assertNotNull($query->first());
    }

    public function test_model_audits_history(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $order->update(['status' => 'processing']);
        $order->update(['status' => 'completed']);

        $audits = $order->audits;

        $this->assertCount(3, $audits);

        $latest = $order->getLatestAudit();
        $this->assertEquals('updated', $latest->event);

        $first = $order->getFirstAudit();
        $this->assertEquals('created', $first->event);
    }
}
