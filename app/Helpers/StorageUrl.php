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

        // FORCE S3 proxy for paths known to be stored in S3 (signatures and attendance)
        // This ensures they work even if the default disk is set to 'local' (common on Railway/Heroku)
        if ($disk === 's3' || str_starts_with($path, 'signatures/') || str_starts_with($path, 'attendance/')) {
            return route('storage.s3', ['path' => $path]);
        }

        // Fallback: local public disk
        return asset('storage/' . $path);
    }
}
