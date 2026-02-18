<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'category';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'iiko_id',
        'name',
        'description',
        'parent_id',
        'order',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'iiko_id' => 'string',
            'order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
