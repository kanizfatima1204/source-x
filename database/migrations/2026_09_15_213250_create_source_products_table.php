<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);

            $table->decimal('minimum_order_quantity', 12, 2)
                ->default(1);

            $table->string('quality_grade')->default('standard');

            $table->unsignedTinyInteger('quality_score')->default(0);

            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->unique(['source_id', 'product_id']);

            $table->index([
                'product_id',
                'is_available',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_products');
    }
};