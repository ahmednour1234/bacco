<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class ConnectionType extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'connection_types';

    protected $fillable = ['name', 'normalized_name', 'aliases', 'active'];

    protected $casts = ['aliases' => 'array', 'active' => 'boolean'];
}
