<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем поле commission_percent если его нет
        if (!Schema::hasColumn('users', 'commission_percent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('commission_percent')
                      ->nullable()
                      ->default(10)
                      ->after('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('commission_percent');
        });
    }
};