<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_feature_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('search_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('request_count')->default(0);

            $table->unsignedInteger('impression_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('inquiry_count')->default(0);
            $table->unsignedInteger('conversion_count')->default(0);

            $table->decimal('click_rate', 8, 4)->default(0);
            $table->decimal('conversion_rate', 8, 4)->default(0);

            $table->string('preferred_category')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('preferred_budget')->nullable();

            $table->decimal('category_affinity', 8, 4)->default(0);
            $table->decimal('location_affinity', 8, 4)->default(0);
            $table->decimal('budget_affinity', 8, 4)->default(0);

            $table->decimal('engagement_score', 8, 4)->default(0);

            $table->timestamp('last_activity_at')->nullable();

            $table->timestamp('snapshot_at')->useCurrent();

            $table->timestamps();

            $table->unique([
                'user_id',
                'snapshot_at',
            ], 'buyer_feat_snap_user_snap_unique');

            $table->index([
                'user_id',
                'last_activity_at',
            ], 'buyer_feat_snap_user_act_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_feature_snapshots');
    }
};