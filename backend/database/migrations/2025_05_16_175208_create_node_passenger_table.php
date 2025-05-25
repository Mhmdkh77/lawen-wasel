<?php

use App\Models\Booking;
use App\Models\Node;
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
        Schema::create('node_passenger', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Node::class);
            $table->foreignIdFor(User::class, 'passenger_id')->constrained()->cascadeOnDelete();
            $table->unique(['node_id', 'passenger_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_passenger');
    }
};
