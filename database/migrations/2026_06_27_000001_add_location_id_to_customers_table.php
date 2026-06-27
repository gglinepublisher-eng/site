<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('is_blocked')->constrained()->nullOnDelete();
        });

        DB::table('customers')
            ->whereNull('location_id')
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($customerId): void {
                $locationId = DB::table('rentals')
                    ->where('customer_id', $customerId)
                    ->whereNotNull('pickup_location_id')
                    ->orderBy('started_at')
                    ->value('pickup_location_id');

                if ($locationId) {
                    DB::table('customers')->where('id', $customerId)->update(['location_id' => $locationId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
