<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'document_number',
        'name',
        'description',
        'file_path',
        'issue_date',
        'expiry_date',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Типы документов клиента
    const TYPE_PASSPORT_MAIN = 'passport_main';
    const TYPE_PASSPORT_REGISTRATION = 'passport_registration';
    const TYPE_DRIVERS_LICENSE = 'drivers_license';
    const TYPE_ADDITIONAL = 'additional';
    const TYPE_OTHER = 'other';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PASSPORT_MAIN => 'Паспорт (основная страница)',
            self::TYPE_PASSPORT_REGISTRATION => 'Паспорт (прописка)',
            self::TYPE_DRIVERS_LICENSE => 'Водительское удостоверение',
            self::TYPE_ADDITIONAL => 'Дополнительные документы',
            self::TYPE_OTHER => 'Другие документы',
        ];
    }

    // Связи
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Вспомогательные методы
    public function getTypeTextAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Другой';
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    public function getIconAttribute(): string
    {
        $extension = strtolower($this->file_extension);
        
        $icons = [
            'pdf' => 'bi-file-pdf text-danger',
            'jpg' => 'bi-file-image text-info',
            'jpeg' => 'bi-file-image text-info',
            'png' => 'bi-file-image text-info',
        ];

        return $icons[$extension] ?? 'bi-file-earmark';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}