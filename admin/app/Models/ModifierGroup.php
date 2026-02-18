<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModifierGroup extends Model
{
    protected $table = 'modifiergroup';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'iiko_id',
        'name',
        'max_quantity',
        'min_quantity',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'iiko_id' => 'string',
            'product_id' => 'string',
            'max_quantity' => 'integer',
            'min_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class, 'group_id');
    }
}
