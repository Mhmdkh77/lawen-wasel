<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $relationships = [
            ['users', 'city_id', 'locations'],
            ['rides', 'vehicle_id', 'vehicles'],
            ['rides', 'ride_group_id', 'ride_groups'],
            ['bookings', 'passenger_id', 'passengers'],
            ['ride_templates', 'vehicle_id', 'vehicles'],
            ['ride_templates', 'ride_template_group_id', 'ride_template_groups'],
            ['ride_groups', 'driver_id', 'drivers'],
            ['ride_groups', 'location_group_id', 'location_groups'],
            ['ride_template_groups', 'driver_id', 'drivers'],
            ['ride_template_groups', 'location_group_id', 'location_groups'],
            ['location_group_location_rel', 'location_group_id', 'location_groups'],
            ['location_group_location_rel', 'location_id', 'locations'],
        ];

        // Check every relationship before changing any table. Some database engines
        // commit DDL statements immediately, so a late failure could leave a partial migration.
        foreach ($relationships as [$table, $column, $parent]) {
            if (DB::table($table)
                ->leftJoin($parent, "$table.$column", '=', "$parent.id")
                ->whereNotNull("$table.$column")
                ->whereNull("$parent.id")
                ->exists()) {
                throw new RuntimeException("Cannot constrain $table.$column: orphaned references to $parent exist.");
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('locations')->nullOnDelete();
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->restrictOnDelete();
            $table->foreign('ride_group_id')->references('id')->on('ride_groups')->restrictOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('passenger_id')->references('id')->on('passengers')->cascadeOnDelete();
        });

        Schema::table('ride_templates', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->restrictOnDelete();
            $table->foreign('ride_template_group_id')->references('id')->on('ride_template_groups')->restrictOnDelete();
        });

        Schema::table('ride_groups', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->restrictOnDelete();
            $table->foreign('location_group_id')->references('id')->on('location_groups')->restrictOnDelete();
        });

        Schema::table('ride_template_groups', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->restrictOnDelete();
            $table->foreign('location_group_id')->references('id')->on('location_groups')->restrictOnDelete();
        });

        Schema::table('location_group_location_rel', function (Blueprint $table) {
            $table->foreign('location_group_id')->references('id')->on('location_groups')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('location_group_location_rel', function (Blueprint $table) {
            $table->dropForeign(['location_group_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::table('ride_template_groups', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['location_group_id']);
        });

        Schema::table('ride_groups', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['location_group_id']);
        });

        Schema::table('ride_templates', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['ride_template_group_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['passenger_id']);
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['ride_group_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });
    }
};
