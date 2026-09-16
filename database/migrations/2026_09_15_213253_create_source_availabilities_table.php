<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('available_quantity', 14, 2)
                ->default(0);

            $table->string('unit')->default('piece');

            $table->date('available_from')->nullable();

            $table->date('available_until')->nullable();

            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->index([
                'source_id',
                'product_id',
                'is_available',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_availabilities');
    }
};