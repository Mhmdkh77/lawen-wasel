<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->foreignId('ride_template_id')->nullable()->constrained('ride_templates')->nullOnDelete();
            $table->unique(['ride_template_id', 'scheduled_time']);
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropUnique(['ride_template_id', 'scheduled_time']);
            $table->dropConstrainedForeignId('ride_template_id');
        });
    }
};
