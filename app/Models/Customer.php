<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'is_blocked' => 'boolean'];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
