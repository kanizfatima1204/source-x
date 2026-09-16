<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference_code')->unique();

            $table->string('location');

            $table->decimal('min_budget', 12, 2)->nullable();
            $table->decimal('max_budget', 12, 2)->nullable();

            $table->string('quality_requirement')
                ->nullable();

            $table->date('required_by')->nullable();

            $table->string('status')->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_requests');
    }
};