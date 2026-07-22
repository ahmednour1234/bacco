<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class ConnectionStandard extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'connection_standards';

    protected $fillable = ['name', 'normalized_name', 'aliases', 'active'];

    protected $casts = ['aliases' => 'array', 'active' => 'boolean'];
}
