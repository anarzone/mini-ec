<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'address_data',
        'is_default',
    ];

    protected $casts = [
        'type' => AddressType::class,
        'address_data' => 'array',
        'is_default' => 'boolean',
    ];
}
