<?php

use App\Models\Driver;
use App\Models\Location;
use App\Models\Passenger;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\User;
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
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Passenger::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ride::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class, 'passenger_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->decimal('passenger_latitude', 10, 8);
            $table->decimal('passenger_longitude', 11, 8);
            $table->foreignIdFor(Location::class, 'institution_location_id')->constrained('locations')->restrictOnDelete();
            $table->unsignedInteger('nb_seats_requested')->default(1);
            $table->text('notes')->nullable();
            $table->enum('type', ['round_trip', 'one_way'])->default('one_way');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled', 'driver_offered', 'expired'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
