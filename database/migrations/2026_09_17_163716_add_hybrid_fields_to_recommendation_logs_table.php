<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->string('algorithm')->default('hybrid')->after('source');
            $table->string('reason')->nullable()->after('algorithm');

            $table->decimal('rule_score', 8, 2)
                ->default(0)
                ->after('score');

            $table->decimal('product_similarity_score', 8, 2)
                ->default(0)
                ->after('rule_score');

            $table->decimal('buyer_similarity_score', 8, 2)
                ->default(0)
                ->after('product_similarity_score');

            $table->decimal('recency_score', 8, 2)
                ->default(0)
                ->after('buyer_similarity_score');

            $table->decimal('diversity_score', 8, 2)
                ->default(0)
                ->after('recency_score');

            $table->index(['user_id', 'algorithm']);
            $table->index(['product_id', 'algorithm']);
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropIndex([
                'recommendation_logs_user_id_algorithm_index',
            ]);

            $table->dropIndex([
                'recommendation_logs_product_id_algorithm_index',
            ]);

            $table->dropColumn([
                'algorithm',
                'reason',
                'rule_score',
                'product_similarity_score',
                'buyer_similarity_score',
                'recency_score',
                'diversity_score',
            ]);
        });
    }
};
