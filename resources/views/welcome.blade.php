@extends('layouts.app')

@section('title', 'Главная')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Добро пожаловать в CRM для лизинга автомобилей</h4>
            </div>
            <div class="card-body">
                <p>Система управления клиентами, сделками и автомобилями.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">👥 Клиенты</h5>
                                <p class="card-text">Управление базой клиентов</p>
                                <a href="{{ route('clients.index') }}" class="btn btn-primary">Перейти</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">📝 Сделки</h5>
                                <p class="card-text">Создание и управление сделками</p>
                                <a href="#" class="btn btn-secondary">Скоро</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">🚗 Автомобили</h5>
                                <p class="card-text">Учет автомобилей</p>
                                <a href="#" class="btn btn-secondary">Скоро</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection