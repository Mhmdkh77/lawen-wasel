<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LocationSeeder::class); // create cities + institutions
        Location::factory(20)->create(); // create stations

        $this->call(AdminDemoSeeder::class);
        $this->call(ShowcaseSeeder::class);
    }
}
