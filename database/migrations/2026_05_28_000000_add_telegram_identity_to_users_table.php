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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable()->unique();
            $table->string('telegram_chat_id')->nullable()->index();
            $table->string('telegram_username')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_user_id']);
            $table->dropIndex(['telegram_chat_id']);
            $table->dropIndex(['telegram_username']);
            $table->dropColumn([
                'telegram_user_id',
                'telegram_chat_id',
                'telegram_username',
            ]);
        });
    }
};
