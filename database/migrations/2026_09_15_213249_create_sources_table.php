<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();

            $table->string('reference_code')->unique();

            $table->string('name')->nullable();

            $table->string('type')->default('supplier');

            $table->string('status')->default('active');

            $table->unsignedTinyInteger('quality_score')->default(0);

            $table->unsignedTinyInteger('performance_score')->default(0);

            $table->text('internal_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};