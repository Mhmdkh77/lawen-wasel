<?php

use App\Models\Route;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

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
            $table->foreignIdFor(Route::class, 'first_route')->constrained()->restrictOnDelete();
            // $table->foreignIdFor(Route::class, 'second_route')->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(Vehicle::class)->constrained()->restrictOnDelete();
            $table->enum('status', ['pending', 'active',  'completed'])->default('pending');
            // $table->enum('type', ['round_trip', 'one_way'])->default('round_trip');
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
