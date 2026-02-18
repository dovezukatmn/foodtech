<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'orderitem';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'product_id',
        'quantity',
        'price',
        'modifiers_json',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'product_id' => 'string',
            'order_id' => 'string',
            'quantity' => 'integer',
            'price' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
