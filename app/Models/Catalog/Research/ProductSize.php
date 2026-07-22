<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class ProductSize extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'product_sizes';

    protected $fillable = [
        'display_value', 'normalized_value', 'nominal_size',
        'unit', 'dn_value', 'inch_decimal', 'sort_order',
    ];

    protected $casts = [
        'inch_decimal' => 'decimal:4',
        'sort_order'   => 'integer',
    ];
}
