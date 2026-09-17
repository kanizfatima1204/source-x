<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_training_samples', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('recommendation_log_id')
                ->nullable()
                ->constrained('recommendation_logs')
                ->nullOnDelete();

            $table->string('algorithm')->nullable();
            $table->string('variant')->nullable();

            $table->decimal('recommendation_score', 8, 4)->nullable();

            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('inquiries')->default(0);
            $table->unsignedInteger('conversions')->default(0);

            $table->decimal('engagement_label', 8, 4)->default(0);

            $table->boolean('clicked')->default(false);
            $table->boolean('converted')->default(false);

            $table->json('buyer_features')->nullable();
            $table->json('product_features')->nullable();
            $table->json('recommendation_features')->nullable();

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_event_at')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'product_id',
            ]);

            $table->index([
                'algorithm',
                'variant',
            ]);

            $table->index('engagement_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_training_samples');
    }
};
