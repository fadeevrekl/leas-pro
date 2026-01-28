<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Car;
use App\Models\Deal;
use Illuminate\Console\Command;

class FixAllStatuses extends Command
{
    protected $signature = 'fix:all-statuses';
    protected $description = 'Полное исправление статусов клиентов и автомобилей';

    public function handle()
    {
        $this->info('Исправление статусов клиентов...');
        
        $clients = Client::all();
        foreach ($clients as $client) {
            $oldStatus = $client->status;
            $client->updateStatusBasedOnDeals();
            
            if ($oldStatus !== $client->status) {
                $this->info("Клиент {$client->id}: {$oldStatus} -> {$client->status}");
            }
        }
        
        $this->info('Исправление статусов автомобилей...');
        
        $cars = Car::all();
        foreach ($cars as $car) {
            $oldStatus = $car->status;
            $car->updateStatusBasedOnDeals();
            
            if ($oldStatus !== $car->status) {
                $this->info("Автомобиль {$car->id}: {$oldStatus} -> {$car->status}");
            }
        }
        
        $this->info('Исправление статусов сделок (проверка завершенных)...');
        
        $completedDeals = Deal::where('status', 'completed')->get();
        foreach ($completedDeals as $deal) {
            // Проверяем, что клиент и автомобиль имеют правильные статусы
            $deal->client->updateStatusBasedOnDeals();
            $deal->car->updateStatusBasedOnDeals();
        }
        
        $this->info('Готово!');
    }
}