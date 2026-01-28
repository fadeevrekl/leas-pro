<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Добавляем investor_id если его нет
            if (!Schema::hasColumn('cars', 'investor_id')) {
                $table->foreignId('investor_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('users')
                      ->onDelete('set null')
                      ->comment('Владелец автомобиля (инвестор)');
            }
            
            // Добавляем manager_id если его нет
            if (!Schema::hasColumn('cars', 'manager_id')) {
                $table->foreignId('manager_id')
                      ->nullable()
                      ->after('investor_id')
                      ->constrained('users')
                      ->onDelete('set null')
                      ->comment('Ответственный менеджер');
            }
            
            // Добавляем status если его нет
            if (!Schema::hasColumn('cars', 'status')) {
                $table->enum('status', ['available', 'in_deal', 'maintenance', 'sold'])
                      ->default('available')
                      ->after('manager_id')
                      ->comment('Статус автомобиля');
            }
            
            // Добавляем price если его нет
            if (!Schema::hasColumn('cars', 'price')) {
                $table->decimal('price', 12, 2)
                      ->nullable()
                      ->after('status')
                      ->comment('Цена автомобиля');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Удаляем только если они существуют
            $columns = ['investor_id', 'manager_id', 'status', 'price'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('cars', $column)) {
                    if (in_array($column, ['investor_id', 'manager_id'])) {
                        // Имя внешнего ключа может отличаться
                        $foreignKeyName = 'cars_' . $column . '_foreign';
                        if (Schema::hasForeignKey('cars', $foreignKeyName)) {
                            $table->dropForeign([$foreignKeyName]);
                        }
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};