<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::where('uploaded_by', admin()->id)->paginate();
        return view('admin.media.index', compact('media'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',   // Limit size to 10MB
        ]);

        $file = $request->file('file');
        $directory = 'media/uploads';
        $filePath = FileManager::uploadPrivate($file, $directory);

        $media = new Media();
        $media->file_name = $file->getClientOriginalName();
        $media->file_path = $filePath;
        $media->file_type = $file->getMimeType();
        $media->uploaded_by = admin()->id;
        $media->save();

        return to_route('admin.media.index')->withSuccess('File uploaded successfully');
    }

    public function show($mediaFileId)
    {
        if (admin()->id == 1) {
            $media = Media::findOrFail($mediaFileId);
        } else {
            $media = Media::where('uploaded_by', admin()->id)->findOrFail($mediaFileId);
        }


        $filePath = $media->file_path;
        
        if (Storage::disk('local')->exists($filePath)) {
            $mimeType = Storage::disk('local')->mimeType($filePath);

            return Response::make(Storage::disk('local')
                ->get($filePath), 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }

        abort(404, 'File not found');
    }
}
