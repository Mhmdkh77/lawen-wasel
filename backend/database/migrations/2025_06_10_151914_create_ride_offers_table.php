<?php

use App\Models\Driver;
use App\Models\Location;
use App\Models\RideRequest;
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
        Schema::create('ride_offers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(RideRequest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Driver::class)->constrained()->cascadeOnDelete();
            $table->decimal('offered_price', 10, 2);
            $table->foreignIdFor(Location::class, 'suggested_pickup_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->decimal('suggested_pickup_latitude', 10, 8)->nullable();
            $table->decimal('suggested_pickup_longitude', 11, 8)->nullable();
            $table->dateTime('suggested_pickup_time')->nullable();
            $table->text('driver_message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->unique(['ride_request_id', 'driver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_offers');
    }
};
