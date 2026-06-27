<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\LandingSetting;
use App\Models\Location;
use App\Models\Photo;

class LandingController extends Controller
{
    public function __invoke()
    {
        $settings = LandingSetting::main();
        abort_unless($settings->is_published || auth()->user()?->isAdmin(), 404);

        $locations = Location::with('city')
            ->withCount([
                'bikes',
                'bikes as available_bikes_count' => fn ($query) => $query->where('status', 'available'),
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $heroPhoto = Photo::where('imageable_type', Bike::class)
            ->latest()
            ->value('path');

        return view('landing.show', [
            'settings' => $settings,
            'locations' => $locations,
            'heroPhoto' => $heroPhoto,
            'totalBikes' => Bike::count(),
            'availableBikes' => Bike::where('status', 'available')->count(),
        ]);
    }
}
