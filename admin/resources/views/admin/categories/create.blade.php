@extends('adminlte::page')

@section('title', 'Создание категории')

@section('content_header')
    <h1>Новая категория</h1>
@stop

@section('content')
    <div class="card card-primary">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Название</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" name="slug" class="form-control">
                    <small class="form-text text-muted">Оставьте пустым для автогенерации</small>
                </div>
                <div class="form-group">
                    <label>Порядок сортировки</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" checked>
                    <label class="form-check-label" for="isActive">Активна</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@stop
