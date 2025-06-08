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
            $table->foreignIdFor(Location::class)->nullable()->constrained()->restrictOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->foreignIdFor(Location::class, 'destination_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('type', ['pickup', 'dropoff']);
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
