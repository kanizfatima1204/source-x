<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('buyer_requests', function (Blueprint $table) {
                $table->dropUnique('buyer_requests_reference_code_unique');
            });
        } catch (\Throwable) {}

        try {
            Schema::table('buyer_requests', function (Blueprint $table) {
                $table->dropIndex('buyer_requests_user_id_status_index');
            });
        } catch (\Throwable) {}

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            try {
                \Illuminate\Support\Facades\DB::statement('DROP INDEX IF EXISTS buyer_requests_reference_code_unique');
            } catch (\Throwable) {}
            try {
                \Illuminate\Support\Facades\DB::statement('DROP INDEX IF EXISTS buyer_requests_user_id_status_index');
            } catch (\Throwable) {}
        }

        Schema::table('buyer_requests', function (Blueprint $table) {
            $toDrop = array_filter([
                Schema::hasColumn('buyer_requests', 'reference_code') ? 'reference_code' : null,
                Schema::hasColumn('buyer_requests', 'min_budget')     ? 'min_budget'     : null,
                Schema::hasColumn('buyer_requests', 'max_budget')     ? 'max_budget'     : null,
                Schema::hasColumn('buyer_requests', 'quality_requirement') ? 'quality_requirement' : null,
                Schema::hasColumn('buyer_requests', 'required_by')   ? 'required_by'   : null,
                Schema::hasColumn('buyer_requests', 'status')         ? 'status'         : null,
                Schema::hasColumn('buyer_requests', 'notes')          ? 'notes'          : null,
            ]);
            if (! empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }

            if (! Schema::hasColumn('buyer_requests', 'category')) {
                $table->string('category')->nullable()->after('user_id');
            }
            $table->string('location')->nullable()->change();
            if (! Schema::hasColumn('buyer_requests', 'budget_level')) {
                $table->enum('budget_level', ['low', 'medium', 'high'])->nullable()->after('location');
            }
            if (! Schema::hasColumn('buyer_requests', 'description')) {
                $table->text('description')->nullable()->after('budget_level');
            }

            try { $table->index(['user_id', 'category']); } catch (\Throwable) {}
        });
    }

    public function down(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            try { $table->dropIndex(['user_id', 'category']); } catch (\Throwable) {}

            $table->dropColumn('description');
            $table->dropColumn('budget_level');
            $table->dropColumn('category');

            $table->string('reference_code')->unique()->after('user_id');
            $table->string('location')->nullable(false)->change();
            $table->decimal('min_budget', 12, 2)->nullable()->after('location');
            $table->decimal('max_budget', 12, 2)->nullable()->after('min_budget');
            $table->string('quality_requirement')->nullable()->after('max_budget');
            $table->date('required_by')->nullable()->after('quality_requirement');
            $table->string('status')->default('draft')->after('required_by');
            $table->text('notes')->nullable()->after('status');
        });
    }
};