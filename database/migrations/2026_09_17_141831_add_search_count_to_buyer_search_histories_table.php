<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyer_search_histories', function (Blueprint $table) {
            $table->unsignedInteger('search_count')
                ->default(1)
                ->after('budget_level');
        });
    }

    public function down(): void
    {
        Schema::table('buyer_search_histories', function (Blueprint $table) {
            $table->dropColumn('search_count');
        });
    }
};
