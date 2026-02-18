@extends('adminlte::page')

@section('title', 'Редактирование категории')

@section('content_header')
    <h1>Редактирование категории</h1>
@stop

@section('content')
    <div class="card card-warning">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Название</label>
                    <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" name="slug" class="form-control" value="{{ $category->slug }}">
                </div>
                <div class="form-group">
                    <label>Порядок сортировки</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive"
                        {{ $category->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">Активна</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Обновить</button>
            </div>
        </form>
    </div>
@stop
