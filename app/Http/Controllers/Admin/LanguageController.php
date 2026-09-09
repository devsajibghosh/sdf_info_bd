<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function keywords()
    {
        $projectRoot  = base_path();
        $translations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectRoot, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (strpos($file->getPathname(), '/vendor/') !== false) {
                continue;
            }

            if ($file->isFile() && preg_match('/\.(blade\.php|php)$/', $file->getFilename())) {
                $contents = file_get_contents($file->getPathname());

                preg_match_all("/__\(\s*['\"](.*?)['\"]\s*\)/", $contents, $matches1);
                preg_match_all("/@lang\(\s*['\"](.*?)['\"]\s*\)/", $contents, $matches2);

                $translations = array_merge($translations, $matches1[1], $matches2[1]);
            }
        }

        $translations = array_unique($translations);
        sort($translations);

        return response()->json([
            'status' => 'success',
            'keywords' => array_values($translations),
        ]);
    }

    public function list()
    {
        $title = 'Languages';

        $languages = Language::latest()->paginate();

        return view('admin.setting.language.list', compact('title', 'languages'));
    }

    public function save(Request $request, $id = null)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:languages,code,' . $id,
        ]);

        $lagnage       = $id ? Language::findOrFail($id) : new Language();
        $lagnage->name = $request->name;
        $lagnage->code = $request->code;
        $lagnage->save();

        return back()->withSuccess(__('Language saved successfully'));
    }
}
