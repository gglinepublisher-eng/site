<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $guarded = [];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function bikes(): HasMany
    {
        return $this->hasMany(Bike::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
