<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoDriverLicenseFixtures
{
    public static function paths(): array
    {
        return array_map(
            fn (int $number) => "driver_licenses/dl{$number}.jpg",
            range(1, 5),
        );
    }

    public static function install(): void
    {
        $private = Storage::disk('local');

        foreach (self::paths() as $path) {
            if ($private->exists($path)) {
                continue;
            }

            $source = database_path('seeders/assets/' . $path);
            $contents = file_get_contents($source);

            if ($contents === false || ! $private->put($path, $contents)) {
                throw new RuntimeException("Could not install demo driver license $path.");
            }
        }
    }
}
