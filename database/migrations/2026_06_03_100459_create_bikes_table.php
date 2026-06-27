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
        Schema::create('bikes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('model');
            $table->string('serial_number')->nullable();
            $table->string('motor')->nullable();
            $table->string('battery')->nullable();
            $table->date('received_at')->nullable();
            $table->date('commissioned_at')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->decimal('depreciation_cost', 12, 2)->default(0);
            $table->decimal('remaining_payment', 12, 2)->default(0);
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bikes');
    }
};
