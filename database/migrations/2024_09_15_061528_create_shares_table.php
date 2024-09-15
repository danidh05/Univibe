<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The user who shares the post
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // The post being shared
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade'); // The recipient (nullable)
            $table->enum('share_type', ['user', 'feed', 'link'])->default('user'); // Type of share
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
