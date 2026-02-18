<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'product';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'iiko_id',
        'name',
        'description',
        'price',
        'weight',
        'image_url',
        'is_deleted',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'iiko_id' => 'string',
            'category_id' => 'string',
            'price' => 'float',
            'weight' => 'float',
            'is_deleted' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function modifierGroups(): HasMany
    {
        return $this->hasMany(ModifierGroup::class, 'product_id');
    }
}
