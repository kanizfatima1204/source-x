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
                'clicked'
            )) {
                $table->boolean('clicked')
                    ->default(false)
                    ->after('score_breakdown');
            }

            if (! Schema::hasColumn(
                'recommendation_logs',
                'clicked_at'
            )) {
                $table->timestamp('clicked_at')
                    ->nullable()
                    ->after('clicked');
            }

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
                'conversion_at'
            )) {
                $table->timestamp('conversion_at')
                    ->nullable()
                    ->after('clicked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $columns = [
                'clicked',
                'clicked_at',
                'algorithm',
                'conversion_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn(
                    'recommendation_logs',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
