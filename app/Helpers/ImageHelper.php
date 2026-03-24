<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Upload a profile avatar to the public disk.
     *
     * Stores the file under storage/app/public/avatars/{uuid}.{ext}
     * and deletes the previous file if one existed.
     *
     * @param  UploadedFile   $file     The incoming uploaded image file.
     * @param  string|null    $oldPath  The existing avatar path to delete (optional).
     * @return string                   Relative path suitable for storing in the DB,
     *                                  e.g. "avatars/abc123.jpg".
     */
    public static function uploadAvatar(UploadedFile $file, ?string $oldPath = null): string
    {
        // Delete old avatar if it exists and is not the default
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;

        $file->storeAs('avatars', $filename, 'public');

        return 'avatars/' . $filename;
    }

    /**
     * Return the full URL for an avatar path, falling back to a generated
     * UI-avatar if no image is stored.
     *
     * @param  string|null  $path   The relative path stored in the DB.
     * @param  string       $name   The user's name (used for the fallback).
     * @return string
     */
    public static function avatarUrl(?string $path, string $name = 'U'): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Fallback: return null so Blade can render the initials avatar
        return '';
    }
}
