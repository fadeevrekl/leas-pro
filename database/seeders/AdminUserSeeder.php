<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@crm-lease.ru',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'phone' => '+7 (999) 123-45-67',
            'notes' => 'Главный администратор системы',
        ]);

        // Создаем тестового менеджера
        User::create([
            'name' => 'Иванов Иван',
            'email' => 'manager@crm-lease.ru',
            'password' => Hash::make('manager123'),
            'role' => User::ROLE_MANAGER,
            'phone' => '+7 (999) 765-43-21',
            'notes' => 'Менеджер по аренде',
        ]);

        // Создаем тестового инвестора
        User::create([
            'name' => 'Петров Петр',
            'email' => 'investor@crm-lease.ru',
            'password' => Hash::make('investor123'),
            'role' => User::ROLE_INVESTOR,
            'phone' => '+7 (999) 111-22-33',
            'notes' => 'Инвестор с 5 автомобилями',
        ]);

        echo "✅ Пользователи созданы:\n";
        echo "Админ: admin@crm-lease.ru / admin123\n";
        echo "Менеджер: manager@crm-lease.ru / manager123\n";
        echo "Инвестор: investor@crm-lease.ru / investor123\n";
    }
}