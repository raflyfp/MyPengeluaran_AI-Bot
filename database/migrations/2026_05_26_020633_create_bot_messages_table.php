<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 40);
            $table->text('message');
            $table->jsonb('parsed_data')->nullable();
            $table->string('status', 40)->default('pending');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['user_id', 'created_at']);
            $table->index(['platform', 'status']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE bot_messages ADD CONSTRAINT bot_messages_platform_check CHECK (platform IN ('telegram', 'whatsapp'))");
            DB::statement("ALTER TABLE bot_messages ADD CONSTRAINT bot_messages_status_check CHECK (status IN ('pending', 'received', 'parsed', 'failed'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_messages');
    }
};
