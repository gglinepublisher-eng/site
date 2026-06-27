<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $guarded = [];

    public static function main(): self
    {
        return self::firstOrCreate(
            ['key' => 'main'],
            [
                'brand_name' => 'ВелоУчёт',
                'hero_title' => 'Аренда самокатов рядом с вами',
                'hero_subtitle' => 'Быстрые городские поездки, понятные условия и живые точки выдачи.',
                'primary_cta' => 'Позвонить и забронировать',
                'phone' => '+7 900 000-00-00',
                'address' => 'Укажите основной адрес',
                'working_hours' => 'Ежедневно 10:00-22:00',
                'telegram' => '',
                'price_note' => 'От 300 ₽ в час',
                'feature_speed' => 'Выдача за 5 минут',
                'feature_service' => 'Исправная техника',
                'feature_locations' => 'Удобные точки в городе',
                'is_published' => true,
            ]
        );
    }
}
