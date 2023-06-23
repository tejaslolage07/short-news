<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->enum('source', ['bingApi', 'newsDataIoApi', 'api', 'scraper'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->enum('source', ['api', 'scraper'])->change();
        });
    }
};
