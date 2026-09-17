<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            $table->dropColumn('reference_code');
            $table->dropColumn('min_budget');
            $table->dropColumn('max_budget');
            $table->dropColumn('quality_requirement');
            $table->dropColumn('required_by');
            $table->dropColumn('status');
            $table->dropColumn('notes');

            $table->string('category')->nullable()->after('user_id');
            $table->string('location')->nullable()->change();
            $table->enum('budget_level', ['low', 'medium', 'high'])->nullable()->after('location');
            $table->text('description')->nullable()->after('budget_level');

            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'category']);

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