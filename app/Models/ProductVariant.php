<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class ProductVariant extends Model
{
    use HasFactory, Searchable, SoftDeletes;

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

    #[SearchUsingFullText(['sku', 'attributes_searchable'])]
    public function toSearchableArray(): array
    {
        return [
            'sku' => $this->sku,
            'attributes_searchable' => $this->attributes_searchable,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'product_variants_index';
    }
}
