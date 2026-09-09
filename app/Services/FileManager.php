<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class FileManager
{
    /**
     * Quality used when re-encoding uploaded images (JPEG/WebP).
     */
    private const IMAGE_QUALITY = 82;

    /**
     * Upload a file to the public disk (accessible via URL).
     *
     * Images are automatically re-compressed/resized: aspect ratio is
     * preserved, images are never upscaled, and output prefers WebP
     * (falling back to the original format if WebP isn't available).
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $oldFile
     * @param array|null $resize ['width' => int, 'height' => int]
     * @return string
     */
    public static function uploadPublic(UploadedFile $file, string $directory, ?string $oldFile = null, ?array $resize = null): string
    {
        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            return self::storeRaw($file, $directory, $oldFile);
        }

        return self::storeOptimizedImage($file, $directory, $oldFile, $resize);
    }

    /**
     * Store a non-image file as-is (unchanged legacy behavior).
     */
    private static function storeRaw(UploadedFile $file, string $directory, ?string $oldFile): string
    {
        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        $filename = uniqid('', true) . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Minimum memory_limit reserved for decoding+encoding a single image.
     * Validation already caps incoming images at 8000x8000px, but a 64MP
     * photo still needs a large contiguous buffer once GD decodes it.
     * memory_limit (unlike upload_max_filesize/post_max_size) is PHP_INI_ALL,
     * so it can be safely raised at runtime for just this operation.
     */
    private const MIN_PROCESSING_MEMORY_LIMIT_BYTES = 512 * 1024 * 1024;

    /**
     * Decode, resize, re-compress and store an uploaded image.
     *
     * Callers only invoke this when a new file was actually submitted
     * (e.g. `$request->hasFile('image')`), so an unchanged image on an
     * edit never reaches here and is never reprocessed.
     */
    private static function storeOptimizedImage(UploadedFile $file, string $directory, ?string $oldFile, ?array $resize): string
    {
        self::ensureProcessingMemory();

        try {
            // ImageManager's default config auto-rotates using EXIF orientation
            // data on read, so no manual orientation handling is needed here.
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            if ($resize && isset($resize['width'], $resize['height'])) {
                // scaleDown preserves aspect ratio and never upscales a smaller image.
                $image->scaleDown($resize['width'], $resize['height']);
            }

            [$encoded, $extension] = self::encode($image, $file);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'image' => __('The uploaded file is not a valid or supported image.'),
            ]);
        }

        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        $path = $directory . '/' . uniqid('', true) . '.' . $extension;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Bump memory_limit for this request only if it's currently lower than
     * what's needed (and not already unlimited, i.e. "-1").
     */
    private static function ensureProcessingMemory(): void
    {
        $current = ini_get('memory_limit');

        if ($current === '-1') {
            return;
        }

        if (self::iniToBytes($current) < self::MIN_PROCESSING_MEMORY_LIMIT_BYTES) {
            ini_set('memory_limit', self::MIN_PROCESSING_MEMORY_LIMIT_BYTES);
        }
    }

    /**
     * Convert a php.ini shorthand size (e.g. "128M", "1G") to bytes.
     */
    private static function iniToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Encode the image, preferring WebP for smaller file size while keeping
     * a safe format-preserving fallback for environments/images where WebP
     * encoding isn't available.
     *
     * @return array{0: \Intervention\Image\Interfaces\EncodedImageInterface, 1: string}
     */
    private static function encode(ImageInterface $image, UploadedFile $file): array
    {
        if (function_exists('imagewebp')) {
            try {
                return [$image->encode(new WebpEncoder(quality: self::IMAGE_QUALITY, strip: true)), 'webp'];
            } catch (\Throwable $e) {
                // Fall through to a format-preserving encode below.
            }
        }

        if (strtolower($file->getClientOriginalExtension()) === 'png') {
            return [$image->encode(new PngEncoder()), 'png'];
        }

        return [$image->encode(new JpegEncoder(quality: self::IMAGE_QUALITY, progressive: true, strip: true)), 'jpg'];
    }

    /**
     * Upload a file to the private (local) disk (not publicly accessible).
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $oldFile
     * @return string
     */
    public static function uploadPrivate(UploadedFile $file, string $directory, ?string $oldFile = null): string
    {
        // Remove old file if it exists
        if ($oldFile && Storage::disk('local')->exists($oldFile)) {
            Storage::disk('local')->delete($oldFile);
        }

        return Storage::disk('local')->put($directory, $file);
    }

    /**
     * Get the full URL to a publicly stored file.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function getPublicUrl(?string $path): ?string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return null;
    }
}
