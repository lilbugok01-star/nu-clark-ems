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
            try {
                // Generate a presigned URL valid for 24 hours to bypass 403 Forbidden on private buckets
                return Storage::disk('s3')->temporaryUrl($path, now()->addHours(24));
            } catch (\Exception $e) {
                // Fallback if temporaryUrl is unsupported by the specific S3 driver
                return Storage::disk('s3')->url($path);
            }
        }

        // Fallback: local public disk
        return asset('storage/' . $path);
    }
}
