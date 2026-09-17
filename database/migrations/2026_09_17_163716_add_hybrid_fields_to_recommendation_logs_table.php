<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('recommendation_logs', 'algorithm')) {
                $table->string('algorithm')->default('hybrid')->after('source');
            }
            if (! Schema::hasColumn('recommendation_logs', 'reason')) {
                $table->string('reason')->nullable()->after('algorithm');
            }
            if (! Schema::hasColumn('recommendation_logs', 'rule_score')) {
                $table->decimal('rule_score', 8, 2)
                    ->default(0)
                    ->after('score');
            }
            if (! Schema::hasColumn('recommendation_logs', 'product_similarity_score')) {
                $table->decimal('product_similarity_score', 8, 2)
                    ->default(0)
                    ->after('rule_score');
            }
            if (! Schema::hasColumn('recommendation_logs', 'buyer_similarity_score')) {
                $table->decimal('buyer_similarity_score', 8, 2)
                    ->default(0)
                    ->after('product_similarity_score');
            }
            if (! Schema::hasColumn('recommendation_logs', 'recency_score')) {
                $table->decimal('recency_score', 8, 2)
                    ->default(0)
                    ->after('buyer_similarity_score');
            }
            if (! Schema::hasColumn('recommendation_logs', 'diversity_score')) {
                $table->decimal('diversity_score', 8, 2)
                    ->default(0)
                    ->after('recency_score');
            }

            try { $table->index(['user_id', 'algorithm']); } catch (\Throwable) {}
            try { $table->index(['product_id', 'algorithm']); } catch (\Throwable) {}
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            try { $table->dropIndex(['recommendation_logs_user_id_algorithm_index']); } catch (\Throwable) {}
            try { $table->dropIndex(['recommendation_logs_product_id_algorithm_index']); } catch (\Throwable) {}

            $columns = ['algorithm', 'reason', 'rule_score', 'product_similarity_score', 'buyer_similarity_score', 'recency_score', 'diversity_score'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('recommendation_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
