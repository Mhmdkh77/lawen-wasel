<?php

use App\Models\Location;
use App\Models\RideTemplate;
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
        Schema::create('ride_tamplate_location', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(RideTemplate::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class)->constrained()->cascadeOnDelete();
            $table->enum('type', ['pickup', 'dropoff']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_tamplate_location');
    }
};
