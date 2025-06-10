<?php

use App\Models\Location;
use App\Models\LocationGroup;
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
        Schema::create('location_group_location_rel', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(LocationGroup::class);
            $table->foreignIdFor(Location::class);
            $table->enum('location_type', ['passenger', 'institution']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_group_location_rel');
    }
};
