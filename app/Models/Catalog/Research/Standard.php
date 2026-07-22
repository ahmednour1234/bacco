<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'standards';

    protected $fillable = ['code', 'name', 'organization', 'description'];
}
