<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('country')->default('Bangladesh');
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index([
                'district',
                'city',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_locations');
    }
};