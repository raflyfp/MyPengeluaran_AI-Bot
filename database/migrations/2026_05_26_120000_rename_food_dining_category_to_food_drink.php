<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldCategory = DB::table('categories')
            ->where('name', 'Food & Dining')
            ->where('type', 'expense')
            ->first();

        if (! $oldCategory) {
            return;
        }

        $newCategory = DB::table('categories')
            ->where('name', 'Food & Drink')
            ->where('type', 'expense')
            ->first();

        if ($newCategory) {
            DB::table('transactions')
                ->where('category_id', $oldCategory->id)
                ->update(['category_id' => $newCategory->id]);

            DB::table('categories')
                ->where('id', $oldCategory->id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('categories')
            ->where('id', $oldCategory->id)
            ->update([
                'name' => 'Food & Drink',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $existingOldCategory = DB::table('categories')
            ->where('name', 'Food & Dining')
            ->where('type', 'expense')
            ->first();

        if ($existingOldCategory) {
            return;
        }

        $category = DB::table('categories')
            ->where('name', 'Food & Drink')
            ->where('type', 'expense')
            ->whereNull('deleted_at')
            ->first();

        if (! $category) {
            return;
        }

        DB::table('categories')
            ->where('id', $category->id)
            ->update([
                'name' => 'Food & Dining',
                'updated_at' => now(),
            ]);
    }
};
