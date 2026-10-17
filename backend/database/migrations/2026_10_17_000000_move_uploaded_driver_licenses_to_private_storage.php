<?php

use App\Services\DriverLicenseStorage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        foreach ($public->allFiles('driver_licenses') as $path) {
            if (DriverLicenseStorage::isDemoFile($path)) {
                continue;
            }

            if (! $private->exists($path)) {
                $stream = $public->readStream($path);

                try {
                    if ($stream === false || ! $private->writeStream($path, $stream)) {
                        throw new RuntimeException("Could not copy driver license $path to private storage.");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            if (hash_file('sha256', $private->path($path)) !== hash_file('sha256', $public->path($path))) {
                throw new RuntimeException("Driver license $path did not copy correctly.");
            }

            if (! $public->delete($path)) {
                throw new RuntimeException("Could not remove publicly accessible driver license $path.");
            }
        }
    }

    public function down(): void
    {
        // Private documents must not be moved back into web-accessible storage.
    }
};
