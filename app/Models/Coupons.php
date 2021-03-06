<?php

namespace App\Models;

use Overtrue\LaravelLike\Traits\Likeable;

class Coupons extends Model
{
    use Likeable;
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
