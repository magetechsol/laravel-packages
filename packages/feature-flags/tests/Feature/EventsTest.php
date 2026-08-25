<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\TestCase;

class EventsTest extends TestCase
{
    public function test_feature_created_event_dispatched(): void
    {
        $this->app['config']->set('mts-feature-flags.events.dispatch_created', true);

        $events = [];
        $this->app['events']->listen(\MageTech\FeatureFlags\Events\FeatureCreated::class, function ($event) use (&$events) {
            $events[] = $event;
        });

        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'event-test',
            'name' => 'Event Test',
            'type' => 'boolean',
        ]);

        $this->assertCount(1, $events);
        $this->assertSame('event-test', $events[0]->flag->key);
    }

    public function test_feature_enabled_event_dispatched(): void
    {
        $this->app['config']->set('mts-feature-flags.events.dispatch_enabled', true);

        $events = [];
        $this->app['events']->listen(\MageTech\FeatureFlags\Events\FeatureEnabled::class, function ($event) use (&$events) {
            $events[] = $event;
        });

        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'enable-event',
            'name' => 'Enable Event',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $service->enable('enable-event');

        $this->assertCount(1, $events);
    }

    public function test_feature_disabled_event_dispatched(): void
    {
        $this->app['config']->set('mts-feature-flags.events.dispatch_disabled', true);

        $events = [];
        $this->app['events']->listen(\MageTech\FeatureFlags\Events\FeatureDisabled::class, function ($event) use (&$events) {
            $events[] = $event;
        });

        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'disable-event',
            'name' => 'Disable Event',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $service->disable('disable-event');

        $this->assertCount(1, $events);
    }

    public function test_feature_deleted_event_dispatched(): void
    {
        $this->app['config']->set('mts-feature-flags.events.dispatch_deleted', true);

        $events = [];
        $this->app['events']->listen(\MageTech\FeatureFlags\Events\FeatureDeleted::class, function ($event) use (&$events) {
            $events[] = $event;
        });

        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'delete-event',
            'name' => 'Delete Event',
            'type' => 'boolean',
        ]);

        $service->delete($flag);

        $this->assertCount(1, $events);
        $this->assertSame('delete-event', $events[0]->key);
    }
}
