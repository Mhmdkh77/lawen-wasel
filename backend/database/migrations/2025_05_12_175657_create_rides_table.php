<?php

use App\Models\RideGroup;
use App\Models\User;
use App\Models\Vehicle;
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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();   
            $table->timestamps();
            $table->foreignIdFor(Vehicle::class);
            $table->foreignIdFor(RideGroup::class);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->dateTime('scheduled_time');
            $table->enum('type', ['to_institution', 'from_institution']);
            $table->unsignedInteger("booked_seats")->default(0);
            $table->unsignedInteger("available_seats")->default(0);
            $table->enum('status', ['pending', 'active',  'completed', 'canceled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
