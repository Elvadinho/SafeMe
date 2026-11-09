<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Saves uploaded files on the PRIVATE disk.
 * Shared by the Children and Documents modules.
 */
class FileService
{
    private const MAX_PHOTO_SIDE = 1600; // pixels

    /**
     * Saves a photo as a clean JPEG.
     * Re-drawing the image removes its hidden EXIF data: phone photos contain the
     * GPS position where they were taken, which could reveal a child's home.
     */
    public function storePhoto(UploadedFile $file, string $folder): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw new RuntimeException('The uploaded file is not a readable image.');
        }

        $image = $this->shrink($image);

        ob_start();
        imagejpeg($image, null, 85);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);

        $path = $folder . '/' . Str::random(40) . '.jpg';
        Storage::disk($this->disk())->put($path, $jpeg);

        return $path;
    }

    /**
     * Saves any other file (PDF scan, photo of an ID card) as it is,
     * under a random name. The client's file name is never used.
     */
    public function storeFile(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder . '/' . now()->format('Y/m'), $this->disk());

        if ($path === false) {
            throw new RuntimeException('Could not save the uploaded file.');
        }

        return $path;
    }

    public function delete(array $paths): void
    {
        $paths = array_filter($paths);

        if ($paths !== []) {
            Storage::disk($this->disk())->delete($paths);
        }
    }

    private function disk(): string
    {
        return config('safeme.disk', 'local');
    }

    /** Big phone photos (4000px) are resized: smaller files, same quality on screen. */
    private function shrink(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $largest = max($width, $height);

        if ($largest <= self::MAX_PHOTO_SIDE) {
            return $image;
        }

        $ratio = self::MAX_PHOTO_SIDE / $largest;
        $resized = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio));
        imagedestroy($image);

        return $resized;
    }
}
