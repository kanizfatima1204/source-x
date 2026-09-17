<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('buyer_requests', 'reference_code')) {
                $table->string('reference_code')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('buyer_requests', 'status')) {
                $table->string('status')->default('submitted')->after('user_id');
            }

            if (!Schema::hasColumn('buyer_requests', 'min_budget')) {
                $table->decimal('min_budget', 12, 2)->nullable()->after('location');
            }

            if (!Schema::hasColumn('buyer_requests', 'max_budget')) {
                $table->decimal('max_budget', 12, 2)->nullable()->after('min_budget');
            }

            if (!Schema::hasColumn('buyer_requests', 'quality_requirement')) {
                $table->string('quality_requirement')->nullable()->after('max_budget');
            }

            if (!Schema::hasColumn('buyer_requests', 'required_by')) {
                $table->date('required_by')->nullable()->after('quality_requirement');
            }

            if (!Schema::hasColumn('buyer_requests', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                Schema::hasColumn('buyer_requests', 'notes') ? 'notes' : null,
                Schema::hasColumn('buyer_requests', 'required_by') ? 'required_by' : null,
                Schema::hasColumn('buyer_requests', 'quality_requirement') ? 'quality_requirement' : null,
                Schema::hasColumn('buyer_requests', 'max_budget') ? 'max_budget' : null,
                Schema::hasColumn('buyer_requests', 'min_budget') ? 'min_budget' : null,
                Schema::hasColumn('buyer_requests', 'status') ? 'status' : null,
                Schema::hasColumn('buyer_requests', 'reference_code') ? 'reference_code' : null,
            ]);

            if (!empty($columnsToDrop)) {
                $table->dropColumn(array_values($columnsToDrop));
            }
        });
    }
};
