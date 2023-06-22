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
            $table->text('short_news')->nullable(false);
            $table->text('headline')->nullable(false);
            $table->string('author', 256)->nullable(true);
            $table->foreignId('news_website_id')->nullable(true)->constrained('news_websites')->on('news_websites')->onDelete('set null');
            $table->text('article_url', 512)->nullable(false);
            $table->text('image_url')->nullable(true);
            $table->text('article_s3_filename')->nullable(false);
            $table->timestamp('published_at')->nullable(false);
            $table->timestamps();
            $table->timestamp('fetched_at')->nullable(false);
            $table->enum('source', ['api', 'scraper'])->nullable(false);
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
