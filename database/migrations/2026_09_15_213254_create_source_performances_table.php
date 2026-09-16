<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_performances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->unsignedInteger('cancelled_orders')->default(0);
            $table->unsignedInteger('late_orders')->default(0);

            $table->unsignedTinyInteger('rating')->default(0);

            $table->unsignedTinyInteger('performance_score')
                ->default(0);

            $table->timestamps();

            $table->unique('source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_performances');
    }
};