<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->foreignId('news_website_id')
                    ->nullable()
                    ->constrained('news_websites')
                    ->onDelete('set null')
                    ->onUpdate('cascade')
                ;
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('news_website_id');
            });
        }
    }
};