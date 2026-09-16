<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_score', 5, 2);

            $table->unsignedInteger('rank');

            $table->string('confidence')
                ->default('medium');

            $table->string('status')
                ->default('recommended');

            $table->boolean('is_algorithm_selected')
                ->default(true);

            $table->boolean('is_admin_selected')
                ->default(false);

            $table->text('summary')->nullable();

            $table->timestamps();

            $table->unique([
                'buyer_request_id',
                'source_id',
            ]);

            $table->index([
                'buyer_request_id',
                'rank',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};