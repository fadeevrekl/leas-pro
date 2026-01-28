<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавляем commission_percent только если его нет
            if (!Schema::hasColumn('users', 'commission_percent')) {
                $table->decimal('commission_percent', 5, 2)
                      ->nullable()
                      ->default(10.00)
                      ->after('role')
                      ->comment('Процент комиссии для инвесторов');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'commission_percent')) {
                $table->dropColumn('commission_percent');
            }
        });
    }
};