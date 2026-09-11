<?php

namespace App\Helpers;

class ImageOptimizer
{
    /**
     * Optimize an uploaded image file in-place.
     *
     * @param string $filePath  Absolute path to the image file
     * @param int    $maxWidth  Maximum width in pixels (height auto-scaled)
     * @param int    $quality   JPEG quality (1-100)
     * @return bool  True if optimization was performed
     */
    public static function optimize(string $filePath, int $maxWidth = 1200, int $quality = 80): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        [$origWidth, $origHeight] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        // Create image resource based on type
        $srcImage = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($filePath),
            'image/png'  => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            'image/gif'  => @imagecreatefromgif($filePath),
            default      => null,
        };

        if (!$srcImage) {
            return false;
        }

        // Calculate new dimensions
        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) round($origHeight * $ratio);
        }

        // Only process if resizing needed OR file is large (> 500KB)
        $fileSize = filesize($filePath);
        if ($origWidth <= $maxWidth && $fileSize < 500 * 1024) {
            imagedestroy($srcImage);
            return false;
        }

        // Create resized image
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as JPEG for photos (better compression)
        // Keep PNG for small/transparent images
        $saved = false;
        if ($mimeType === 'image/png' && $fileSize > 500 * 1024) {
            // Convert large PNGs to JPEG
            $jpegPath = preg_replace('/\.png$/i', '.jpg', $filePath);
            $saved = imagejpeg($dstImage, $jpegPath, $quality);
            if ($saved && $jpegPath !== $filePath) {
                @unlink($filePath);
            }
        } elseif ($mimeType === 'image/png') {
            $saved = imagepng($dstImage, $filePath, 8);
        } elseif ($mimeType === 'image/webp') {
            $saved = imagewebp($dstImage, $filePath, $quality);
        } else {
            // Default to JPEG
            $saved = imagejpeg($dstImage, $filePath, $quality);
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $saved;
    }
}
