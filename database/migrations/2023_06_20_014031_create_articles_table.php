<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('short_news');
            $table->text('headline');
            $table->string('author', 256)->nullable();
            $table->text('article_url',512);
            $table->text('image_url')->nullable();
            $table->text('article_s3_filename');
            $table->timestamp('published_at');
            $table->timestamps();
            $table->timestamp('fetched_at');
            $table->enum('source', ['api', 'scraper']);
            $table->text('country')->nullable();
            $table->enum('language', ['ja', 'en'])->nullable();
            $table->text('category')->nullable();
            $table->text('keywords')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
