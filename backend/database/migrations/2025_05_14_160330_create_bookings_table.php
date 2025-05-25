<?php

use App\Models\Ride;
use App\Models\Station;
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
            $table->foreignIdFor(Ride::class, "first_ride_id")->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ride::class, 'second_ride_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'passenger_id');
            $table->enum('status', ['pending', 'accepted', 'canceled', 'rejected'])->default('pending');
            $table->unsignedInteger('nb_seats')->default(1);
            $table->dateTime('booking_time');
            $table->enum('type', ['round_trip', 'one_way'])->default('one_way');
            $table->decimal('price', 10, 2);
            $table->check('first_ride != second_ride');
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
