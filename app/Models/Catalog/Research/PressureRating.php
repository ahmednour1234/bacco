<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class PressureRating extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'pressure_ratings';

    protected $fillable = [
        'rating_name', 'numeric_value', 'unit',
        'pressure_class', 'service_type', 'normalized_value',
    ];

    protected $casts = ['numeric_value' => 'decimal:3'];
}
