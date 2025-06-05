<?php

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
            $table->foreignIdFor(User::class, 'driver_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Vehicle::class);
            $table->dateTime('start_time');
            $table->dateTime('finish_time');
            $table->dateTime('arrival_time');
            $table->unsignedInteger("booked_seats")->default(0);
            $table->enum('status', ['pending', 'active',  'completed'])->default('pending');
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
