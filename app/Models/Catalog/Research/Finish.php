<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class Finish extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'finishes';

    protected $fillable = ['name', 'normalized_name', 'active'];

    protected $casts = ['active' => 'boolean'];
}
