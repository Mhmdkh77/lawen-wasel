<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/villages.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);


        foreach ($csv as $record) {
            Location::create(
                [
                    'name' => trim($record['Location_Name_En']),
                    'type' => 'city',
                    'latitude' => trim($record['Latitude']),
                    'longitude' => trim($record['Longitude']),
                ]
            );
        }

        Location::create([
            'name' => 'Lebanese University',
            'type' => 'institution',
            'latitude' => round(33.37749271448064, 8),
            'longitude' => round(35.49657674861225, 8),
        ]);

        Location::create([
            'name' => 'LIU',
            'type' => 'institution',
            'latitude' => round(33.37702701277116, 8),
            'longitude' => round(35.49739394648093, 8),
        ]);

        Location::create([
            'name' => 'AL-Afaq',
            'type' => 'institution',
            'latitude' => round(33.388636140822626, 8),
            'longitude' => round(35.48883862064127, 8),
        ]);
    }
}
