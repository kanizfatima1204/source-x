<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_experiments', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();

            $table->string('description')->nullable();

            $table->string('status')->default('draft');

            $table->string('control_algorithm')->default('v1');

            $table->string('variant_algorithm')->default('v2');

            $table->unsignedTinyInteger('traffic_percentage')->default(50);

            $table->unsignedBigInteger('minimum_sample_size')->default(100);

            $table->timestamp('started_at')->nullable();

            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_experiments');
    }
};
