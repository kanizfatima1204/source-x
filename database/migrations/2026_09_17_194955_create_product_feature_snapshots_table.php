<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_feature_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->string('budget_level')->nullable();

            $table->decimal('price_score', 8, 4)->default(0);
            $table->decimal('popularity_score', 8, 4)->default(0);
            $table->decimal('availability_score', 8, 4)->default(0);
            $table->decimal('verification_score', 8, 4)->default(0);

            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('request_count')->default(0);

            $table->unsignedInteger('recommendation_impressions')->default(0);
            $table->unsignedInteger('recommendation_clicks')->default(0);
            $table->unsignedInteger('recommendation_inquiries')->default(0);
            $table->unsignedInteger('recommendation_conversions')->default(0);

            $table->decimal('ctr', 8, 4)->default(0);
            $table->decimal('conversion_rate', 8, 4)->default(0);

            $table->decimal('quality_score', 8, 4)->default(0);

            $table->timestamp('snapshot_at')->useCurrent();

            $table->timestamps();

            $table->unique([
                'product_id',
                'snapshot_at',
            ], 'prod_feat_snap_prod_snap_unique');

            $table->index([
                'category',
                'location',
            ], 'prod_feat_snap_cat_loc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_feature_snapshots');
    }
};