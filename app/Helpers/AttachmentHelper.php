<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

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
        // If it's a valid URL, return as-is
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return $input;
        }

        // Check if base64
        if (preg_match('/^data:([\w\/+]+);base64,/', $input, $matches)) {
            $mime = $matches[1];
            $base64 = substr($input, strpos($input, ',') + 1);
            $data = base64_decode($base64);

            $folder = public_path('attachments');
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $filename = Str::random(20);
            if (Str::startsWith($mime, 'image/')) {
                // Convert image to webp
                $image = Image::make($data)->encode('webp', 90);
                $filepath = $folder . '/' . $filename . '.webp';
                $image->save($filepath);
                return asset('attachments/' . $filename . '.webp');
            } elseif ($mime === 'application/pdf') {
                $filepath = $folder . '/' . $filename . '.pdf';
                file_put_contents($filepath, $data);
                return asset('attachments/' . $filename . '.pdf');
            } elseif (Str::startsWith($mime, 'video/')) {
                $ext = explode('/', $mime)[1];
                $filepath = $folder . '/' . $filename . '.' . $ext;
                file_put_contents($filepath, $data);
                return asset('attachments/' . $filename . '.' . $ext);
            }
        }

        // If not valid, return empty string
        return '';
    }
}
