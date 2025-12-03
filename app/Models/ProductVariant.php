<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'price_cents',
        'currency',
        'weight_g',
        'is_active',
        'product_id',
        'attributes',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'weight_g' => 'integer',
        'is_active' => 'boolean',
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}