<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('pending');

            $table->string('verification_type')
                ->default('manual');

            $table->text('notes')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index([
                'source_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_verifications');
    }
};