<?php

use App\Models\Driver;
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
        Schema::create('ride_template_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Driver::class);
            $table->foreignIdFor(LocationGroup::class);
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_template_groups');
    }
};
