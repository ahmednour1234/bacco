<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class OperationType extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'operation_types';

    protected $fillable = ['name', 'normalized_name', 'active'];

    protected $casts = ['active' => 'boolean'];
}
