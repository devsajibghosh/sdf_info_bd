<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload(UploadedFile $file, string $path, string $disk = 'public'): string
    {
        return $file->store($path, $disk);
    }

    public function delete(string $filePath, string $disk = 'public'): void
    {
        Storage::disk($disk)->delete($filePath);
    }

    public function getUrl(string $filePath, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($filePath);
    }

    public function exists(string $filePath, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($filePath);
    }

    public function download(string $filePath, string $disk = 'protected')
    {
        if (!Storage::disk($disk)->exists($filePath)) {
            abort(404);
        }

        return Storage::disk($disk)->download($filePath);
    }
}
