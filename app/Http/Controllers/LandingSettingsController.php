<?php

namespace App\Http\Controllers;

use App\Models\LandingSetting;
use Illuminate\Http\Request;

class LandingSettingsController extends Controller
{
    public function edit()
    {
        return view('landing.edit', ['settings' => LandingSetting::main()]);
    }

    public function update(Request $request)
    {
        LandingSetting::main()->update($request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'primary_cta' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'price_note' => ['nullable', 'string', 'max:255'],
            'feature_speed' => ['nullable', 'string', 'max:255'],
            'feature_service' => ['nullable', 'string', 'max:255'],
            'feature_locations' => ['nullable', 'string', 'max:255'],
            'is_published' => ['boolean'],
        ]));

        return to_route('landing.edit')->with('success', 'Лендинг обновлен');
    }
}
