<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverLicenseStorage
{
    private const DEMO_FILES = [
        'driver_licenses/dl1.jpg',
        'driver_licenses/dl2.jpg',
        'driver_licenses/dl3.jpg',
        'driver_licenses/dl4.jpg',
        'driver_licenses/dl5.jpg',
    ];

    public static function isDemoFile(string $path): bool
    {
        return in_array($path, self::DEMO_FILES, true);
    }

    public function response(?string $path): StreamedResponse
    {
        abort_unless($path && str_starts_with($path, 'driver_licenses/') && ! str_contains($path, '..'), 404);

        $disk = Storage::disk('local');

        if (! $disk->exists($path) && self::isDemoFile($path)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
