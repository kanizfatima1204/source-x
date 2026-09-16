<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_reasons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_result_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('factor');

            $table->decimal('score', 5, 2);

            $table->decimal('weight', 5, 2);

            $table->decimal('weighted_score', 6, 2);

            $table->string('status')->default('matched');

            $table->text('message');

            $table->timestamps();

            $table->index([
                'match_result_id',
                'factor',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_reasons');
    }
};