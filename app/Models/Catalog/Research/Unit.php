<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'units';

    protected $fillable = ['code', 'name', 'type', 'active'];

    protected $casts = ['active' => 'boolean'];
}
