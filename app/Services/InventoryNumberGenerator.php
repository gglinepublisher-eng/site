<?php

namespace App\Services;

use App\Models\Bike;
use App\Models\Location;

class InventoryNumberGenerator
{
    public function generate(Location $location): string
    {
        $prefix = strtoupper($location->city->code.'-'.$location->code.'-'.now()->format('y'));
        $last = Bike::where('number', 'like', $prefix.'-%')->orderByDesc('number')->value('number');
        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
