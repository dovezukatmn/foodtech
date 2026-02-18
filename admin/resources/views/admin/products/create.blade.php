@extends('adminlte::page')

@section('title', 'Новый товар')

@section('content_header')
    <h1>Новый товар</h1>
@stop

@section('content')
    <div class="card card-primary">
        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Название</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Категория</label>
                            <select name="category_id" class="form-control">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Цена (₽)</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Вес (г)</label>
                            <input type="number" name="weight" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>URL картинки</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive"
                                checked>
                            <label class="form-check-label" for="isActive">Товар активен</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@stop
