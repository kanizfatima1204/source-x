<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('buyer_requests', 'request_count')) {
                $table->unsignedInteger('request_count')
                    ->default(1)
                    ->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buyer_requests', function (Blueprint $table) {
            if (Schema::hasColumn('buyer_requests', 'request_count')) {
                $table->dropColumn('request_count');
            }
        });
    }
};