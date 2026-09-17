<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('recommendation_log_id')
                ->nullable()
                ->constrained('recommendation_logs')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('event_type');

            $table->decimal('recommendation_score', 8, 2)
                ->nullable();

            $table->string('algorithm')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index([
                'user_id',
                'event_type',
            ]);

            $table->index([
                'product_id',
                'event_type',
            ]);

            $table->index([
                'recommendation_log_id',
                'event_type',
            ]);

            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_events');
    }
};
