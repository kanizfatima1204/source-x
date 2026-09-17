<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('recommendation_logs', 'clicked')) {
                $table->boolean('clicked')->default(false)->after('source');
            }
            if (! Schema::hasColumn('recommendation_logs', 'clicked_at')) {
                $table->timestamp('clicked_at')->nullable()->after('clicked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropColumn([
                'clicked',
                'clicked_at',
            ]);
        });
    }
};
