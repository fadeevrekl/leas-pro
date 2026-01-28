<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'passport_main',      // Паспорт (основная страница)
                'passport_registration', // Паспорт (прописка)
                'drivers_license',    // Водительское удостоверение
                'additional',         // Дополнительные документы
                'other'               // Другие
            ]);
            $table->string('document_number')->nullable(); // Номер документа
            $table->string('name');                       // Название документа
            $table->text('description')->nullable();      // Описание
            $table->string('file_path');                  // Путь к файлу
            $table->date('issue_date')->nullable();       // Дата выдачи
            $table->date('expiry_date')->nullable();      // Срок действия
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};