<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MageTech\QueryToolkit\QueryToolkitServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders(): array
    {
        return [
            QueryToolkitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp(): void
    {
        ['config']->set('database.default', 'testing');
        ['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        ['config']->set('mts-query.parameters.filter', 'filter');
        ['config']->set('mts-query.parameters.sort', 'sort');
        ['config']->set('mts-query.parameters.include', 'include');
        ['config']->set('mts-query.parameters.fields', 'fields');
        ['config']->set('mts-query.parameters.search', 'search');
        ['config']->set('mts-query.parameters.per_page', 'per_page');
        ['config']->set('mts-query.parameters.page', 'page');
    }

    protected function setUp(): void
    {
        parent::setUp();

        ->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        Schema::create('users', function (Blueprint ) {
            ->id();
            ->string('name');
            ->string('email')->unique();
            ->boolean('is_active')->default(true);
            ->decimal('salary', 10, 2)->default(0);
            ->string('status')->default('active');
            ->timestamps();
        });

        Schema::create('posts', function (Blueprint ) {
            ->id();
            ->foreignId('user_id')->constrained()->onDelete('cascade');
            ->string('title');
            ->text('body');
            ->boolean('is_published')->default(false);
            ->string('category')->nullable();
            ->timestamps();
        });

        Schema::create('comments', function (Blueprint ) {
            ->id();
            ->foreignId('post_id')->constrained()->onDelete('cascade');
            ->text('body');
            ->timestamps();
        });

        Schema::create('categories', function (Blueprint ) {
            ->id();
            ->string('name');
            ->string('slug')->unique();
            ->foreignId('parent_id')->nullable()->constrained('categories');
            ->timestamps();
        });

        Schema::create('products', function (Blueprint ) {
            ->id();
            ->string('name');
            ->string('sku')->unique();
            ->text('description')->nullable();
            ->decimal('price', 10, 2);
            ->integer('stock_quantity')->default(0);
            ->boolean('is_active')->default(true);
            ->json('metadata')->nullable();
            ->timestamps();
        });
    }
}
