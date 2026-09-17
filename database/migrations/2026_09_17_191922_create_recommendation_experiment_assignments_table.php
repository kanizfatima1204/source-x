<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_experiment_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('experiment_id')
                ->constrained('recommendation_experiments')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('variant');

            $table->string('algorithm');

            $table->timestamp('assigned_at');

            $table->timestamps();

            $table->unique([
                'experiment_id',
                'user_id',
            ], 'rec_exp_assign_exp_user_unique');

            $table->index([
                'experiment_id',
                'variant',
            ], 'rec_exp_assign_exp_variant_idx');

            $table->index([
                'user_id',
                'experiment_id',
            ], 'rec_exp_assign_user_exp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_experiment_assignments');
    }
};