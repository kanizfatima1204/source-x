<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_search_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('keyword')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();

            $table->enum('budget_level', [
                'low',
                'medium',
                'high',
            ])->nullable();

            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_search_histories');
    }
};
