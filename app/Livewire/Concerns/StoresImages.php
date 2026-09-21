<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Storage;

trait StoresImages
{
    /** Saves an uploaded image on the public disk and removes the one it replaces (full URLs are left alone). */
    protected function replaceImage($upload, string $directory, ?string $old): ?string
    {
        if (!$upload) {
            return $old;
        }

        $path = $upload->store($directory, 'public');

        if ($old && !str_starts_with($old, 'http') && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        return $path;
    }

    /** Temporary preview URL of a not-yet-saved upload (null when it is not a previewable image). */
    protected function previewUrl($upload): ?string
    {
        return $upload && method_exists($upload, 'isPreviewable') && $upload->isPreviewable() ? $upload->temporaryUrl() : null;
    }
}
