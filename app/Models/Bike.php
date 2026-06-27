<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Bike extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'commissioned_at' => 'date',
            'purchase_cost' => 'decimal:2',
            'depreciation_cost' => 'decimal:2',
            'remaining_payment' => 'decimal:2',
            'warranty_until' => 'date',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'imageable');
    }
}
