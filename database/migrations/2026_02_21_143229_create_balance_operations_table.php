<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balance_operations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('balance_id')
                ->constrained('balances')
                ->cascadeOnDelete()
                ->comment('Related balance row');

            $table->string('type', 32)->comment('deposit|withdraw|fee|adjustment');

            $table->enum('direction', ['credit', 'debit'])
                ->comment('credit = increase, debit = decrease');

            $table->decimal('amount', 36, 18)->comment('Operation amount');

            $table->string('status', 32)
                ->default('pending')
                ->comment('pending|confirmed|failed');

            $table->string('external_id')
                ->nullable()
                ->index()
                ->comment('Idempotency key / external tx hash / provider operation id');

            $table->timestamps();

            // (опционально, но полезно) запретить дубль одной и той же external_id в рамках баланса
            $table->unique(['balance_id', 'external_id'], 'balance_ops_balance_external_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_operations');
    }
};
