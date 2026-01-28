<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'type',
        'document_number',
        'issue_date',
        'expiry_date',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Типы документов
    const TYPE_PTS = 'pts';
    const TYPE_STS = 'sts';
    const TYPE_OSAGO = 'osago';
    const TYPE_KASKO = 'kasko';
    const TYPE_ADDITIONAL_INSURANCE = 'additional_insurance';
    const TYPE_AUTOTEKA = 'autoteka';
    const TYPE_SERVICE_DOCS = 'service_docs';
    const TYPE_OTHER = 'other';

    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_PTS => 'ПТС',
            self::TYPE_STS => 'СТС',
            self::TYPE_OSAGO => 'ОСАГО',
            self::TYPE_KASKO => 'КАСКО',
            self::TYPE_ADDITIONAL_INSURANCE => 'Доп. страхование',
            self::TYPE_AUTOTEKA => 'Автотека',
            self::TYPE_SERVICE_DOCS => 'Сервисные документы',
            self::TYPE_OTHER => 'Другие',
        ];
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getTypeTextAttribute(): string
    {
        return self::getDocumentTypes()[$this->type] ?? 'Другой';
    }

    public function getDocumentNameAttribute(): string
    {
        $type = $this->getTypeTextAttribute();
        return $this->document_number ? "{$type} №{$this->document_number}" : $type;
    }
    
     /**
     * Получить URL для просмотра документа
     */
    public function getViewUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '#';
        }
        
        // Извлекаем имя файла из пути
        $filename = basename($this->file_path);
        return route('car.documents.view', ['filename' => $filename]);
    }

    /**
     * Получить URL для скачивания документа
     */
    public function getDownloadUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '#';
        }
        
        // Извлекаем имя файла из пути
        $filename = basename($this->file_path);
        return route('car.documents.download', ['filename' => $filename]);
    }

    /**
     * Получить имя файла
     */
    public function getFilenameAttribute(): string
    {
        return basename($this->file_path);
    }

    /**
     * Проверить, можно ли просмотреть файл в браузере
     */
    public function canViewInBrowser(): bool
    {
        if (!$this->file_path) {
            return false;
        }
        
        $path = storage_path('app/public/' . $this->file_path);
        if (!file_exists($path)) {
            return false;
        }
        
        $mime = mime_content_type($path);
        $viewableTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        return in_array($mime, $viewableTypes);
    }
    
    /**
     * Получить иконку для типа файла
     */
    public function getFileIconAttribute(): string
    {
        if (!$this->file_path) {
            return 'bi-file-earmark';
        }
        
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'bi-file-earmark-pdf',
            'jpg' => 'bi-file-earmark-image',
            'jpeg' => 'bi-file-earmark-image',
            'png' => 'bi-file-earmark-image',
            'gif' => 'bi-file-earmark-image',
            'doc' => 'bi-file-earmark-word',
            'docx' => 'bi-file-earmark-word',
        ];
        
        return $icons[$extension] ?? 'bi-file-earmark';
    }
    



    
}