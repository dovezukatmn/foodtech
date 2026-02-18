<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modifier extends Model
{
    protected $table = 'modifier';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'iiko_id',
        'name',
        'price',
        'description',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'iiko_id' => 'string',
            'group_id' => 'string',
            'price' => 'float',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'group_id');
    }
}
