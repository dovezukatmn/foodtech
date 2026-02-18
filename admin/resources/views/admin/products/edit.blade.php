@extends('adminlte::page')

@section('title', 'Редактирование товара')

@section('content_header')
    <h1>Редактирование: {{ $product->name }}</h1>
@stop

@section('content')
    <div class="card card-warning">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Название</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Категория</label>
                            <select name="category_id" class="form-control">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Цена (₽)</label>
                            <input type="number" name="price" class="form-control" value="{{ $product->price }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Вес (г)</label>
                            <input type="number" name="weight" class="form-control" value="{{ $product->weight }}">
                        </div>
                        <div class="form-group">
                            <label>URL картинки</label>
                            <input type="url" name="image_url" class="form-control" value="{{ $product->image_url }}">
                        </div>
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive"
                                {{ $product->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Товар активен</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Обновить</button>
            </div>
        </form>
    </div>
@stop
