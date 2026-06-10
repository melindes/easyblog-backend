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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content_comment');
            $table->foreignId('article_id')
                  ->constrained('articles')
                  ->onDelete('cascade');
            $table->foreignId('commentator_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->unique(['article_id','commentator_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
