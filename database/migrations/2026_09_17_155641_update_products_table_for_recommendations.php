<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_category_id_is_active_index');
            });
        } catch (\Throwable) {}

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            try {
                \Illuminate\Support\Facades\DB::statement('DROP INDEX IF EXISTS products_category_id_is_active_index');
            } catch (\Throwable) {}
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                try { $table->dropForeign(['category_id']); } catch (\Throwable) {}
                $table->dropColumn('category_id');
            }
            if (Schema::hasColumn('products', 'unit')) {
                $table->dropColumn('unit');
            }
            if (Schema::hasColumn('products', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->after('slug');
            }
            if (! Schema::hasColumn('products', 'location')) {
                $table->string('location')->after('category');
            }
            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 12, 2)->default(0)->after('location');
            }
            if (! Schema::hasColumn('products', 'budget_level')) {
                $table->enum('budget_level', ['low', 'medium', 'high'])->default('medium')->after('price');
            }
            if (! Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('budget_level');
            }
            if (! Schema::hasColumn('products', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_available');
            }
            if (! Schema::hasColumn('products', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('is_verified');
            }
            if (! Schema::hasColumn('products', 'request_count')) {
                $table->unsignedInteger('request_count')->default(0)->after('view_count');
            }
            if (! Schema::hasColumn('products', 'popularity_score')) {
                $table->unsignedInteger('popularity_score')->default(0)->after('request_count');
            }

            try { $table->index(['category', 'location']); } catch (\Throwable) {}
            try { $table->index(['budget_level', 'is_available']); } catch (\Throwable) {}
            try { $table->index('is_verified'); } catch (\Throwable) {}
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try { $table->dropIndex(['category', 'location']); } catch (\Throwable) {}
            try { $table->dropIndex(['budget_level', 'is_available']); } catch (\Throwable) {}
            try { $table->dropIndex(['is_verified']); } catch (\Throwable) {}

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