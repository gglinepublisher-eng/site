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
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bike_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ownership')->default('own');
            $table->string('external_bike')->nullable();
            $table->string('external_owner')->nullable();
            $table->string('external_phone')->nullable();
            $table->date('serviced_at');
            $table->text('problem');
            $table->text('work_done');
            $table->string('mechanic');
            $table->decimal('cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
