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

            $table->index('algorithm');
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropIndex([
                'recommendation_logs_algorithm_index',
            ]);

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
