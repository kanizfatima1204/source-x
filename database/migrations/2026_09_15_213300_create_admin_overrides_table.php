<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('match_result_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedInteger('previous_rank')->nullable();
            $table->unsignedInteger('new_rank')->nullable();

            $table->string('action');

            $table->text('reason');

            $table->timestamps();

            $table->index([
                'buyer_request_id',
                'admin_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_overrides');
    }
};