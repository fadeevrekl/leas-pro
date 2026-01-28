<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('investor_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Владелец автомобиля (инвестор)');
            
            $table->foreignId('manager_id')
                  ->nullable()
                  ->after('investor_id')
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Ответственный менеджер');
            
            $table->enum('status', ['available', 'in_deal', 'maintenance', 'sold'])
                  ->default('available')
                  ->after('manager_id')
                  ->comment('Статус автомобиля');
            
            $table->decimal('price', 12, 2)
                  ->nullable()
                  ->after('status')
                  ->comment('Цена автомобиля');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['investor_id']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['investor_id', 'manager_id', 'status', 'price']);
        });
    }
};