<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class AccessScope
{
    public static function locationId(): ?int
    {
        $user = auth()->user();
        return $user && ! $user->isAdmin() ? $user->location_id : null;
    }

    public static function bikes(Builder $query): Builder
    {
        return $query->when(self::locationId(), fn ($q, $id) => $q->where('location_id', $id));
    }

    public static function rentals(Builder $query): Builder
    {
        return $query->when(self::locationId(), fn ($q, $id) => $q->where(fn ($q) => $q->where('pickup_location_id', $id)->orWhere('return_location_id', $id)));
    }

    public static function service(Builder $query): Builder
    {
        return $query->when(self::locationId(), fn ($q, $id) => $q->where('location_id', $id));
    }
}
