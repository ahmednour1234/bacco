<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'materials';

    protected $fillable = [
        'name', 'normalized_name', 'material_category',
        'standard_designation', 'aliases', 'active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'active'  => 'boolean',
    ];
}
