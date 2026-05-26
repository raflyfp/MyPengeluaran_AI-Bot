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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('type', 24);
            $table->decimal('amount', 14, 2);
            $table->text('note')->nullable();
            $table->string('source', 40)->default('manual');
            $table->timestampTz('transaction_date');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['category_id', 'transaction_date']);
            $table->index(['type', 'transaction_date']);
            $table->index('source');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('income', 'expense'))");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_source_check CHECK (source IN ('manual', 'telegram', 'whatsapp', 'import', 'system'))");
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT transactions_amount_check CHECK (amount >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
