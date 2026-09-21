<?php

namespace App\Support;

class ImageUrl
{
    /** Stored images are paths on the public disk; some (e.g. Google avatars) are already full URLs. */
    public static function for(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : asset('storage/' . ltrim($path, '/'));
    }
}
