<?php

use App\Models\Ride;
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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(User::class, 'rated_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'rating_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Ride::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->check('rating >= 1 AND rating <= 5');
            $table->text("review_text")->nullable();
            $table->unique(columns: ['rated_user_id', 'rating_user_id', 'ride_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
