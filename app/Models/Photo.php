<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['shows_defect' => 'boolean'];
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
