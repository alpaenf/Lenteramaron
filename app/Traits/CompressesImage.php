<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait CompressesImage
{
    /**
     * Compress, resize, and save image directly to public/uploads/ folder.
     * No storage symlink required — file accessible immediately at /uploads/{folder}/{file}.
     */
    protected function compressAndSaveImage(UploadedFile $file, string $folder = 'uploads', int $maxWidth = 1200, int $quality = 82): string
    {
        $realPath = $file->getRealPath();
        $imageInfo = @getimagesize($realPath);
        $mime = $imageInfo['mime'] ?? '';

        // Target: public_path('uploads/{folder}')
        $targetDir = public_path('uploads/' . $folder);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $baseName = uniqid() . '_' . time() . '.jpg';
        $relativePath = 'uploads/' . $folder . '/' . $baseName;
        $fullPath = public_path($relativePath);

        // If GD cannot process this type, copy the raw file
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $baseName = uniqid() . '_' . time() . '.' . $ext;
            $relativePath = 'uploads/' . $folder . '/' . $baseName;
            $fullPath = public_path($relativePath);
            copy($realPath, $fullPath);
            return $relativePath;
        }

        // Load source image using GD
        $sourceImage = null;
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($realPath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($realPath);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($realPath);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($realPath);
                break;
        }

        // Fallback: copy raw file if GD failed
        if (!$sourceImage) {
            copy($realPath, $fullPath);
            return $relativePath;
        }

        $origWidth  = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Resize proportionally if wider than maxWidth
        if ($origWidth > $maxWidth) {
            $newWidth  = $maxWidth;
            $newHeight = (int) round($origHeight * ($maxWidth / $origWidth));
        } else {
            $newWidth  = $origWidth;
            $newHeight = $origHeight;
        }

        // Create canvas with white background (for PNG transparency)
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($targetImage, 255, 255, 255);
        imagefill($targetImage, 0, 0, $white);

        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($sourceImage);

        // Save as JPEG to public folder
        $success = imagejpeg($targetImage, $fullPath, $quality);
        imagedestroy($targetImage);

        // Verify file was written
        if (!$success || !file_exists($fullPath) || filesize($fullPath) < 100) {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            // Final fallback: copy original
            copy($realPath, $fullPath);
        }

        return $relativePath;
    }
}
