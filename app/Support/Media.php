<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    /** Public URL for a stored path. Absolute URLs and site-relative paths pass through untouched. */
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /** Store an upload in the public disk, replacing (and deleting) the previous file if one is given. */
    public static function store(?UploadedFile $file, string $folder, ?string $replace = null): ?string
    {
        if (! $file) {
            return $replace;
        }
        static::delete($replace);

        return $file->store($folder, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://', '/'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
