<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    protected $table = 'test_orders';

    protected $fillable = [
        'total',
        'status',
        'user_id',
        'requires_stock_check',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'requires_stock_check' => 'boolean',
        ];
    }

    public static function createTable(): void
    {
        if (! Schema::hasTable('test_orders')) {
            Schema::create('test_orders', function (Blueprint $table) {
                $table->id();
                $table->decimal('total', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('requires_stock_check')->default(false);
                $table->timestamps();
            });
        }
    }

    public static function dropTable(): void
    {
        Schema::dropIfExists('test_orders');
    }
}
