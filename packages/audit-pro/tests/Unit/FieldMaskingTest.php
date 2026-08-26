<?php

declare(strict_types=1);

use MageTech\Audit\Tests\TestCase;
use MageTech\Audit\Support\ActorData;
use MageTech\Audit\Support\AuditData;

class FieldMaskingTest extends TestCase
{
    public function test_email_masking(): void
    {
        $strategy = config('audit.masking.strategies.email');

        $this->assertEquals('j****@example.com', $strategy('john@example.com'));
        $this->assertEquals('a****@test.com', $strategy('alice@test.com'));
    }

    public function test_phone_masking(): void
    {
        $strategy = config('audit.masking.strategies.phone');

        $result = $strategy('+1234567890');
        $this->assertStringEndsWith('7890', $result);
    }

    public function test_api_token_masking(): void
    {
        $strategy = config('audit.masking.strategies.api_token');

        $result = $strategy('secret-token-12345');
        $this->assertEquals('********', $result);
    }

    public function test_credit_card_masking(): void
    {
        $strategy = config('audit.masking.strategies.credit_card');

        $result = $strategy('4111111111111111');
        $this->assertStringEndsWith('1111', $result);
    }

    public function test_ssn_masking(): void
    {
        $strategy = config('audit.masking.strategies.ssn');

        $result = $strategy('123-45-6789');
        $this->assertEquals('***-**-6789', $result);
    }

    public function test_actor_dataToArray(): void
    {
        $actor = new ActorData(
            type: 'App\\Models\\User',
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
        );

        $array = $actor->toArray();

        $this->assertEquals('App\\Models\\User', $array['type']);
        $this->assertEquals(1, $array['id']);
        $this->assertEquals('John Doe', $array['name']);
        $this->assertEquals('john@example.com', $array['email']);
    }

    public function test_actor_data_is_empty(): void
    {
        $actor = new ActorData();
        $this->assertTrue($actor->isEmpty());

        $actor = new ActorData(type: 'system');
        $this->assertFalse($actor->isEmpty());
    }

    public function test_audit_data_make(): void
    {
        $data = AuditData::make()
            ->event('test')
            ->withMetadata(['key' => 'value'])
            ->withTags(['tag1']);

        $array = $data->toArray();

        $this->assertEquals('test', $array['event']);
        $this->assertEquals(['key' => 'value'], $array['metadata']);
        $this->assertEquals(['tag1'], $array['tags']);
    }
}
