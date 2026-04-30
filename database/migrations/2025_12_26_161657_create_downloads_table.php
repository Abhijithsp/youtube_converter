<?php

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
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('youtube_url');
            $table->string('youtube_id')->index();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->enum('format', ['mp3', 'flac'])->default('mp3');
            $table->enum('quality', ['192', '256', '320'])->default('320');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('progress')->default(0);
            $table->text('download_url')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
