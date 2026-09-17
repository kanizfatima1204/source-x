<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();

            $table->string('name');

            $table->text('description')->nullable();

            $table->string('type')->default('string');

            $table->text('value')->nullable();

            $table->json('options')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_settings');
    }
};
