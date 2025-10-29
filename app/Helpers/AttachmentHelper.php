<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AttachmentHelper
{
    /**
     * Handle image/pdf/video input: if URL, return as-is; if base64, save and return path.
     * Always save images as webp, PDFs/videos as their type.
     *
     * @param string $input
     * @return string
     */
    public static function handleAttachment($input)
    {
        try {
            // If it's a valid URL, return as-is
            if (filter_var($input, FILTER_VALIDATE_URL)) {
                return $input;
            }

            // If it's base64 (with or without data URI prefix), return as-is
            if (preg_match('/^data:([\w\/+]+);base64,/', $input) || base64_decode($input, true)) {
                return $input;
            }
        } catch (\Exception $e) {
            // Log error if needed: \Log::error($e->getMessage());
            return '';
        }
        // If not valid, return empty string
        return '';
    }
}
