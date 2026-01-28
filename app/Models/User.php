<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'notes',
        'is_active',
        'commission_percent',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
    
    
    // Автомобили инвестора
    public function investorCars()
    {
        return $this->hasMany(Car::class, 'investor_id');
    }

    // Автомобили менеджера
    public function managedCars()
    {
        return $this->hasMany(Car::class, 'manager_id');
    }
    
    

    // Роли
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_INVESTOR = 'investor';

    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_MANAGER => 'Менеджер',
            self::ROLE_INVESTOR => 'Инвестор',
        ];
    }

    // Проверки ролей
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isInvestor(): bool
    {
        return $this->role === self::ROLE_INVESTOR;
    }

    public function getRoleTextAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Неизвестно';
    }

    // Связи
    public function cars()
    {
        return $this->hasMany(Car::class, 'manager_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'manager_id');
    }

    public function uploadedDocuments()
    {
        return $this->hasMany(ClientDocument::class, 'uploaded_by');
    }

    // Scope для фильтрации
    public function scopeManagers($query)
    {
        return $query->where('role', self::ROLE_MANAGER)->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeInvestors($query)
    {
        return $query->where('role', self::ROLE_INVESTOR);
    }
    public function hasRole($role): bool
{
    return $this->role === $role;
}

public function hasAnyRole(array $roles): bool
{
    return in_array($this->role, $roles);
}
}
