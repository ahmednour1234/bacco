<?php

namespace App\Repositories\Admin;

use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;

class WebsiteRepository
{
    public function allActive(): Collection
    {
        return Website::where('active', true)->orderBy('name')->get();
    }
}
