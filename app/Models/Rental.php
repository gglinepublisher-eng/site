<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'due_at' => 'date',
            'returned_at' => 'date',
            'payment' => 'decimal:2',
            'deposit' => 'decimal:2',
            'damage_cost' => 'decimal:2',
            'deposit_returned' => 'decimal:2',
        ];
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function returnLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(RentalInspection::class);
    }

    public function handoverInspection()
    {
        return $this->hasOne(RentalInspection::class)->where('type', 'handover');
    }

    public function returnInspection()
    {
        return $this->hasOne(RentalInspection::class)->where('type', 'return');
    }

    public function getStateAttribute(): string
    {
        if ($this->returned_at) {
            return 'closed';
        }

        return $this->due_at->isBefore(today()) ? 'overdue' : 'active';
    }
}
