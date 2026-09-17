<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            if (! Schema::hasColumn(
                'recommendation_logs',
                'algorithm'
            )) {
                $table->string('algorithm')
                    ->nullable()
                    ->after('source');
            }

            if (! Schema::hasColumn(
                'recommendation_logs',
                'clicked'
            )) {
                $table->boolean('clicked')
                    ->default(false)
                    ->after('algorithm');
            }

            if (! Schema::hasColumn(
                'recommendation_logs',
                'clicked_at'
            )) {
                $table->timestamp('clicked_at')
                    ->nullable()
                    ->after('clicked');
            }

            // Safely add the index — ignore if it already exists (MySQL safe)
            try {
                $table->index('algorithm');
            } catch (\Throwable) {
                // Index already exists — safe to ignore
            }
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            try {
                $table->dropIndex([
                    'recommendation_logs_algorithm_index',
                ]);
            } catch (\Throwable) {
                // Index may not exist
            }

            if (Schema::hasColumn(
                'recommendation_logs',
                'clicked_at'
            )) {
                $table->dropColumn('clicked_at');
            }

            if (Schema::hasColumn(
                'recommendation_logs',
                'clicked'
            )) {
                $table->dropColumn('clicked');
            }

            if (Schema::hasColumn(
                'recommendation_logs',
                'algorithm'
            )) {
                $table->dropColumn('algorithm');
            }
        });
    }
};
