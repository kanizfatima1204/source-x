<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('unit');
            $table->dropColumn('is_active');

            $table->string('category')->after('slug');
            $table->string('location')->after('category');
            $table->decimal('price', 12, 2)->default(0)->after('location');
            $table->enum('budget_level', ['low', 'medium', 'high'])->default('medium')->after('price');
            $table->boolean('is_available')->default(true)->after('budget_level');
            $table->boolean('is_verified')->default(false)->after('is_available');
            $table->unsignedInteger('view_count')->default(0)->after('is_verified');
            $table->unsignedInteger('request_count')->default(0)->after('view_count');
            $table->unsignedInteger('popularity_score')->default(0)->after('request_count');

            $table->index(['category', 'location']);
            $table->index(['budget_level', 'is_available']);
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category', 'location']);
            $table->dropIndex(['budget_level', 'is_available']);
            $table->dropIndex(['is_verified']);

            $table->dropColumn('popularity_score');
            $table->dropColumn('request_count');
            $table->dropColumn('view_count');
            $table->dropColumn('is_verified');
            $table->dropColumn('is_available');
            $table->dropColumn('budget_level');
            $table->dropColumn('price');
            $table->dropColumn('location');
            $table->dropColumn('category');

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete()
                ->after('slug');
            $table->string('unit')->default('piece')->after('description');
            $table->boolean('is_active')->default(true)->after('unit');
        });
    }
};