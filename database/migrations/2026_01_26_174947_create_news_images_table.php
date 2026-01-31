<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('news_images')) {
            Schema::create('news_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('news_id');
                $table->string('file_path');
                $table->string('alt_text')->nullable();
                $table->string('caption')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->json('dimensions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');
                $table->index('news_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_images');
    }
};
