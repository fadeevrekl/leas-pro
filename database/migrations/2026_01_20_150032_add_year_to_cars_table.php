<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Добавляем поле year после model
            $table->year('year')->nullable()->after('model');
            
            // Можно также сделать его обязательным:
            // $table->year('year')->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};