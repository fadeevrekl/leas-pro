<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            
            // Основные данные
            $table->string('deal_number')->unique();           // Номер договора
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            
            // Параметры сделки
            $table->enum('deal_type', ['rental', 'lease']);    // Аренда или лизинг
            $table->decimal('total_amount', 12, 2);           // Общая сумма сделки
            $table->decimal('initial_payment', 12, 2)->default(0); // Первоначальный взнос
            $table->integer('payment_count');                 // Количество платежей
            $table->decimal('payment_amount', 10, 2);         // Сумма регулярного платежа
            
            // Сроки
            $table->date('start_date');                       // Дата начала
            $table->date('end_date');                         // Дата окончания
            $table->enum('payment_period', ['day', 'week', 'month']); // Период оплаты
            $table->integer('payment_day')->nullable();       // День недели/месяца для оплаты
            
            // Статусы
            $table->enum('status', [
                'draft',           // Черновик
                'active',          // Активная (договор подписан)
                'completed',       // Завершена
                'cancelled',       // Отменена
                'overdue'          // Просрочена
            ])->default('draft');
            
            // Оповещения
            $table->boolean('sms_notifications')->default(true); // Включены ли SMS
            $table->timestamp('last_sms_sent_at')->nullable();   // Когда последнее SMS отправлено
            $table->integer('sms_count')->default(0);            // Сколько SMS отправлено
            
            // Документы
            $table->string('contract_path')->nullable();      // Путь к подписанному договору
            $table->date('contract_signed_date')->nullable(); // Дата подписания договора
            
            // Дополнительно
            $table->text('notes')->nullable();                // Заметки менеджера
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('deal_number');
            $table->index('status');
            $table->index('end_date');
        });
        
        // Таблица платежей по сделке
        Schema::create('deal_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->integer('payment_number');                // Номер платежа (1, 2, 3...)
            $table->date('due_date');                         // Дата платежа
            $table->decimal('amount', 10, 2);                 // Сумма платежа
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->timestamp('paid_at')->nullable();         // Когда оплачен
            $table->string('payment_method')->nullable();     // наличные/безнал
            $table->string('transaction_id')->nullable();     // ID транзакции
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('due_date');
            $table->index('status');
        });
        
        // Таблица оповещений
        Schema::create('deal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('deal_payments')->nullOnDelete();
            $table->enum('type', ['sms', 'email', 'system']);
            $table->string('phone')->nullable();              // На какой номер отправлено
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed']);
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_notifications');
        Schema::dropIfExists('deal_payments');
        Schema::dropIfExists('deals');
    }
};