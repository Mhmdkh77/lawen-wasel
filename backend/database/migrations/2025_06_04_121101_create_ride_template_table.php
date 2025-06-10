<?php

use App\Models\LocationGroup;
use App\Models\RideGroup;
use App\Models\RideTemplate;
use App\Models\RideTemplateGroup;
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
        Schema::create('ride_template', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Vehicle::class);
            $table->foreignIdFor(RideTemplateGroup::class);
            $table->time('scheduled_time');
            $table->json('recurring_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->enum('type', ['to_institution', 'from_institution']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_template');
    }
};
