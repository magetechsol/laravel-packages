<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Audit\Tests\TestCase;
use MageTech\Audit\Tests\Models\TestOrder;
use MageTech\Audit\Models\Audit;
use MageTech\Audit\Facades\Audit as AuditFacade;

class AuditApiTest extends TestCase
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

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $this->withoutExceptionHandling();
    }

    public function test_audit_index_endpoint(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/audits')
            ->assertOk();
    }

    public function test_audit_show_endpoint(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $audit = Audit::where('event', 'created')
            ->where('auditable_type', TestOrder::class)
            ->first();

        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->getJson("/api/audits/{$audit->uuid}")
            ->assertOk();
    }

    public function test_audit_auditable_endpoint(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $encodedType = urlencode(TestOrder::class);

        $this->actingAs($user)
            ->getJson("/api/auditable/{$encodedType}/{$order->id}")
            ->assertOk();
    }

    public function test_audit_stats_endpoint(): void
    {
        $order = TestOrder::create([
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/audit-stats')
            ->assertOk();
    }
}
