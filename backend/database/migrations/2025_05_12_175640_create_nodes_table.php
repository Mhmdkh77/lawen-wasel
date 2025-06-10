<?php

use App\Models\Location;
use App\Models\Ride;
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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Ride::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class, 'pickup_location')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->foreignIdFor(Location::class, 'dropoff_location')->constrained()->restrictOnDelete();
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);
            $table->enum('status', ['pending', 'completed']);
            $table->unique(['latitude', 'longitude', 'ride_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
