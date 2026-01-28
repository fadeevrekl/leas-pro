<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'payment_id',
        'type',
        'phone',
        'message',
        'status',
        'sent_at',
        'error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Типы уведомлений
    const TYPE_SMS = 'sms';
    const TYPE_EMAIL = 'email';
    const TYPE_SYSTEM = 'system';

    public static function getTypes(): array
    {
        return [
            self::TYPE_SMS => 'SMS',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_SYSTEM => 'Системное',
        ];
    }

    // Статусы
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает отправки',
            self::STATUS_SENT => 'Отправлено',
            self::STATUS_FAILED => 'Ошибка',
        ];
    }

    // Связи
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(DealPayment::class);
    }

    // Вспомогательные методы
    public function getTypeTextAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Неизвестно';
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Неизвестно';
    }

    public function markAsSent(): void
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = now();
        $this->save();
    }

    public function markAsFailed(string $error): void
    {
        $this->status = self::STATUS_FAILED;
        $this->error = $error;
        $this->save();
    }
}