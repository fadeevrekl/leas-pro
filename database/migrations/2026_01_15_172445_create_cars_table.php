<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand');                    // Марка
            $table->string('model');                    // Модель
            $table->string('vin')->unique();            // VIN номер
            $table->string('color');                    // Цвет
            $table->string('license_plate')->unique();  // Гос номер
            $table->integer('mileage')->default(0);     // Пробег
            $table->string('fuel_type');                // Тип топлива (АИ-92, АИ-95 и т.д.)
            $table->foreignId('investor_id')            // Инвестор (связь с users)
      ->nullable()
      ->constrained('users')
      ->nullOnDelete();
            $table->foreignId('manager_id')             // Менеджер
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->decimal('price', 12, 2);            // Цена автомобиля
            $table->string('gps_tracker_id')->nullable(); // ID GPS трекера
            $table->integer('deal_count')->default(0);  // Кол-во сделок
            $table->enum('status', ['available', 'in_deal', 'maintenance', 'sold'])
                  ->default('available');               // Статус
            $table->text('notes')->nullable();          // Заметки
            $table->timestamps();
        });
        
        // Таблица для документов автомобиля
        Schema::create('car_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'pts', 'sts', 'osago', 'kasko', 
                'additional_insurance', 'autoteka', 
                'service_docs', 'other'
            ]);
            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
        // Таблица для расходов на автомобиль
        Schema::create('car_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('expense_type');             // ТО, Ремонт, Мойка и т.д.
            $table->decimal('amount', 10, 2);           // Сумма
            $table->date('expense_date');               // Дата расхода
            $table->string('document_path')->nullable(); // Чек/документ
            $table->text('description')->nullable();    // Описание
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_expenses');
        Schema::dropIfExists('car_documents');
        Schema::dropIfExists('cars');
    }
    public function investor()
{
    return $this->belongsTo(User::class, 'investor_id');
}
};
