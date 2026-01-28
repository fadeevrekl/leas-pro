<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');                    // Фамилия
            $table->string('first_name');                   // Имя
            $table->string('middle_name')->nullable();      // Отчество
            $table->string('passport_series', 4);           // Серия паспорта
            $table->string('passport_number', 6);           // Номер паспорта
            $table->string('passport_issued_by');           // Кем выдан
            $table->date('passport_issued_date');           // Дата выдачи
            $table->string('passport_division_code', 7);    // Код подразделения
            $table->text('registration_address');           // Адрес регистрации
            $table->text('residential_address');            // Адрес проживания
            $table->string('drivers_license')->nullable();  // ВУ
            $table->string('phone', 20);                    // Телефон
            $table->string('additional_phone', 20)->nullable(); // Доп. телефон
            $table->string('guarantor')->nullable();        // Поручитель
            $table->text('notes')->nullable();              // Заметки
            $table->timestamps();                           // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
    public function cars()
{
    return $this->hasMany(Car::class, 'investor_id');
}
};