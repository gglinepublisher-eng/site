<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('brand_name')->default('ВелоУчёт');
            $table->string('hero_title');
            $table->text('hero_subtitle')->nullable();
            $table->string('primary_cta')->default('Позвонить');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('telegram')->nullable();
            $table->string('price_note')->nullable();
            $table->string('feature_speed')->nullable();
            $table->string('feature_service')->nullable();
            $table->string('feature_locations')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
