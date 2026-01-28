{{-- resources/views/themes/switch.blade.php --}}
@extends('layouts.app-tabler')

@section('title', 'Переключение темы')

@section('content')
<div class="container-xl">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Выберите тему</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Tabler Theme -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-palette fa-3x text-primary"></i>
                                    </div>
                                    <h4>Tabler</h4>
                                    <p class="text-muted">Современная тема</p>
                                    <a href="{{ route('themes.switch', ['theme' => 'tabler']) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-check me-2"></i>Выбрать
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bootstrap Theme -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-th-large fa-3x text-secondary"></i>
                                    </div>
                                    <h4>Bootstrap</h4>
                                    <p class="text-muted">Классическая тема</p>
                                    <a href="{{ route('themes.switch', ['theme' => 'bootstrap']) }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-check me-2"></i>Выбрать
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Примечание:</strong> При переключении темы будет выполнена переадресация на главную страницу.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Предварительный просмотр</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h5>Текущая тема: <strong>{{ session('theme', 'tabler') }}</strong></h5>
                            <hr>
                            
                            <h6>Пример компонентов:</h6>
                            
                            <!-- Кнопки -->
                            <div class="mb-3">
                                <button class="btn btn-primary me-2">Primary</button>
                                <button class="btn btn-secondary me-2">Secondary</button>
                                <button class="btn btn-success me-2">Success</button>
                                <button class="btn btn-warning me-2">Warning</button>
                                <button class="btn btn-danger">Danger</button>
                            </div>
                            
                            <!-- Badges -->
                            <div class="mb-3">
                                <span class="badge bg-primary me-2">Primary</span>
                                <span class="badge bg-secondary me-2">Secondary</span>
                                <span class="badge bg-success me-2">Success</span>
                                <span class="badge bg-warning me-2">Warning</span>
                                <span class="badge bg-danger">Danger</span>
                            </div>
                            
                            <!-- Карточка -->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Пример карточки</h4>
                                </div>
                                <div class="card-body">
                                    <p>Это пример карточки в текущей теме.</p>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Название</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>Пример строки</td>
                                                <td><span class="badge bg-success">Активен</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection