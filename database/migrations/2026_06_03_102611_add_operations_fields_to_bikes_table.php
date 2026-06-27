<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('color')->nullable();
            $table->string('frame_number')->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->unsignedInteger('mileage')->default(0);
            $table->string('condition')->default('good');
            $table->date('warranty_until')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn(['color', 'frame_number', 'manufacture_year', 'mileage', 'condition', 'warranty_until']);
        });
    }
};
