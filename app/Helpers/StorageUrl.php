<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    /**
     * Return the correct public URL for a stored file,
     * whether it's on the local 'public' disk or an S3 bucket.
     */
    public static function url(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $disk = config('filesystems.default', 'local');

        if ($disk === 's3') {
            return route('storage.s3', ['path' => $path]);
        }

        // Fallback: local public disk
        return asset('storage/' . $path);
    }
}
