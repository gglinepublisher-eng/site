<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceRecord extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'serviced_at' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'imageable');
    }

    public function getBikeTitleAttribute(): string
    {
        return $this->ownership === 'own'
            ? ($this->bike?->number.' · '.$this->bike?->model)
            : $this->external_bike;
    }
}
