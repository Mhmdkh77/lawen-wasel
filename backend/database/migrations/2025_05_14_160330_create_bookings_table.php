<?php

use App\Models\BookingGroup;
use App\Models\Location;
use App\Models\Node;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Passenger::class);
            $table->foreignIdFor(Ride::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BookingGroup::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RideRequest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Node::class)->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('nb_seats')->default(1);
            $table->decimal('price', 10, 2);
            $table->unique(['ride_id', 'passenger_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
