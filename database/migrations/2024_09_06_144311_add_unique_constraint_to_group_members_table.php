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
        Schema::table('group_members', function (Blueprint $table) {
            // Add unique constraint for the combination of group_chat_id and user_id
            $table->unique(['group_chat_id', 'user_id'], 'group_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            // Drop the unique constraint if rolling back
            $table->dropUnique('group_user_unique');
        });
    }
};