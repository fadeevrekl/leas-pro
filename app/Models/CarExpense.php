<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'expense_type',
        'amount',
        'expense_date',
        'document_path',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    // Типы расходов
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_REPAIR = 'repair';
    const TYPE_WASH = 'wash';
    const TYPE_FUEL = 'fuel';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_TAX = 'tax';
    const TYPE_OTHER = 'other';

    public static function getExpenseTypes(): array
    {
        return [
            self::TYPE_MAINTENANCE => 'ТО (Техобслуживание)',
            self::TYPE_REPAIR => 'Ремонт',
            self::TYPE_WASH => 'Мойка',
            self::TYPE_FUEL => 'Заправка',
            self::TYPE_INSURANCE => 'Страхование',
            self::TYPE_TAX => 'Налог',
            self::TYPE_OTHER => 'Другие расходы',
        ];
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getExpenseTypeTextAttribute(): string
    {
        return self::getExpenseTypes()[$this->expense_type] ?? 'Другие расходы';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ' ') . ' ₽';
    }
 /**
 * Получить URL для просмотра документа расхода
 */
public function getViewUrlAttribute(): string
{
    if (!$this->document_path) {
        return '#';
    }
    
    // Извлекаем имя файла из пути
    $filename = basename($this->document_path);
    return route('car.expenses.document.view', ['filename' => $filename]);
}

/**
 * Получить URL для скачивания документа расхода
 */
public function getDownloadUrlAttribute(): string
{
    if (!$this->document_path) {
        return '#';
    }
    
    // Извлекаем имя файла из пути
    $filename = basename($this->document_path);
    return route('car.expenses.document.download', ['filename' => $filename]);
}

/**
 * Проверить, можно ли просмотреть файл в браузере
 */
public function canViewInBrowser(): bool
{
    if (!$this->document_path) {
        return false;
    }
    
    $path = storage_path('app/public/' . $this->document_path);
    if (!file_exists($path)) {
        return false;
    }
    
    $mime = mime_content_type($path);
    $viewableTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    return in_array($mime, $viewableTypes);
}

/**
 * Получить иконку для типа расхода
 */
public function getExpenseIconAttribute(): string
{
    $icons = [
        'maintenance' => 'fas fa-wrench',
        'repair' => 'fas fa-tools',
        'fuel' => 'fas fa-gas-pump',
        'washing' => 'fas fa-spray-can',
        'insurance' => 'fas fa-shield-alt',
        'tax' => 'fas fa-calculator',
        'other' => 'fas fa-money-bill-wave'
    ];
    
    return $icons[$this->expense_type] ?? 'fas fa-spray-can';
}
    
}