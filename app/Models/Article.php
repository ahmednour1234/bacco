<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'active' => 'boolean',
        ]);
    }
}
