<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'type',
        'name',
        'document_number',
        'description',
        'issue_date',
        'file_path',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    // Типы документов
    const TYPE_CONTRACT = 'contract';
    const TYPE_ADDITIONAL_AGREEMENT = 'additional_agreement';
    const TYPE_ACT = 'act';
    const TYPE_PAYMENT_DOCUMENT = 'payment_document';
    const TYPE_OTHER = 'other';

    public static function getTypes(): array
    {
        return [
            self::TYPE_CONTRACT => 'Договор',
            self::TYPE_ADDITIONAL_AGREEMENT => 'Дополнительное соглашение',
            self::TYPE_ACT => 'Акт',
            self::TYPE_PAYMENT_DOCUMENT => 'Платежный документ',
            self::TYPE_OTHER => 'Другое',
        ];
    }

    // Связи
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Вспомогательные методы
    public function getTypeTextAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Неизвестно';
    }

    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    public function getFileSizeAttribute(): ?int
    {
        if ($this->file_path && file_exists(storage_path('app/public/' . $this->file_path))) {
            return filesize(storage_path('app/public/' . $this->file_path));
        }
        return null;
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $size = $this->file_size;
        if (!$size) return 'Неизвестно';

        if ($size < 1024) {
            return $size . ' Б';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' КБ';
        } else {
            return round($size / 1048576, 2) . ' МБ';
        }
    }
}