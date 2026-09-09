<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SystemHelper;
use App\Models\Page;
use App\Models\Setting; // Import the Setting model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\RequiredIf;

class WebsiteController extends Controller
{
    private $sectionsConfig;

    public function __construct()
    {
        goIfUserCan('manage-website');
        $this->sectionsConfig = SystemHelper::sections();
    }

    public function pages()
    {
        $title = 'Pages';

        $pages = Page::paginate();

        return view('admin.website.pages.list', compact('pages', 'title'));
    }

    public function deletePage($id)
    {
        $page = Page::findOrFail($id);

        if ($page->is_default) {
            return back()->withError(__('You cannot delete the default page'));
        }

        $page->delete();

        return back()->withSuccess(__('Page deleted successfully'));
    }

    public function editPage($pageId)
    {
        $title = 'Edit Pages';

        $page = Page::findOrFail($pageId);

        $sections = $this->sectionsConfig;

        return view('admin.website.pages.edit', compact('page', 'title', 'sections'));
    }

    public function newPage()
    {
        $title = 'Add New Page';

        return view('admin.website.pages.add', compact('title'));
    }

    public function saveNewPage(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:pages,title'
        ]);

        $page        = new Page();
        $page->title = $request->title;
        $page->slug  = str()->slug($request->title);
        $page->save();

        return to_route('admin.website.page.edit', $page->id)->withSuccess('Update the page section');
    }

    public function updatePage(Request $request, $pageId)
    {
        $page = Page::findOrFail($pageId);

        $rules = [
            'ordered_sections'             => 'sometimes',
            'title'                        => $page->is_default ? 'nullable' :  'required',
            'seo_content.meta_title'       => 'nullable|string|max:255',
            'seo_content.meta_description' => 'nullable|string|max:500',
            'seo_content.meta_keywords'    => 'nullable|string|max:255',
            'content' => [new RequiredIf(function () use ($page) {
                return $page->privacy;
            })]
        ];

        $request->validate($rules);


        if (!$page->is_default) {
            $page->title    = $request->title;
            $page->slug     = str()->slug($request->title);
        }

        if (!$page->privacy) {
            $page->sections = $request->ordered_sections ?? [];
            $page->seo_content = (object) ($request->seo_content ?? []);
        } else {
            $page->content = $request->content;
        }

        $page->save();

        return back()->withSuccess(__('Page updated successfully'));
    }

    public function sections()
    {
        $title = 'Website Frontend Sections';
        $sections = $this->sectionsConfig;
        return view('admin.website.sections.list', compact('sections', 'title'));
    }

    public function editSection($key)
    {
        abort_unless(array_key_exists($key, $this->sectionsConfig), 404);

        $title = 'Edit Section: ' . $this->sectionsConfig[$key]['title'];
        $sectionConfig = $this->sectionsConfig[$key]; // Configuration for the section
        $sectionKey = $key;

        $settingKey = 'section_' . $key . '_content';
        $setting = Setting::where('key', $settingKey)->first();
        $contentData = $setting ? $setting->value : []; // value is already an array due to casts

        return view('admin.website.sections.edit', compact('sectionConfig', 'sectionKey', 'title', 'contentData'));
    }

    public function updateSection(Request $request, $key)
    {
        abort_unless(array_key_exists($key, $this->sectionsConfig), 404);

        $sectionConfig = $this->sectionsConfig[$key]['config'];
        $inputData     = $request?->content ?? [];
        $contentToSave = [];

        $uploadPath = "sections/{$key}";
        $settingKey = "section_{$key}_content";

        $existingSetting = Setting::where('key', $settingKey)->first();
        $existingContent = $existingSetting ? $existingSetting->value : [];

        foreach ($sectionConfig as $fieldKey => $field) {
            if ($field['type'] === 'group') {
                $contentToSave[$fieldKey] = [];

                foreach ($field['fields'] as $childKey => $childField) {
                    $currentValue = $existingContent[$fieldKey][$childKey] ?? null;

                    if ($childField['type'] === 'image') {
                        if ($request->hasFile("content.{$fieldKey}.{$childKey}")) {
                            if ($currentValue) {
                                Storage::disk('public')->delete(Str::replaceFirst('storage/', '', $currentValue));
                            }

                            $filePath = $request->file("content.{$fieldKey}.{$childKey}")
                                ->store($uploadPath, 'public');

                            $contentToSave[$fieldKey][$childKey] = "storage/{$filePath}";
                        } else {
                            $contentToSave[$fieldKey][$childKey] = $currentValue;
                        }
                    } else {
                        $contentToSave[$fieldKey][$childKey] = $inputData[$fieldKey][$childKey] ?? null;
                    }
                }
            }

            // Handle repeater fields
            elseif ($field['type'] === 'repeater') {
                $contentToSave[$fieldKey] = [];

                foreach ($inputData[$fieldKey] ?? [] as $index => $item) {
                    $repeaterItemData = [];

                    foreach ($field['fields'] as $subKey => $subField) {
                        $currentValue = $existingContent[$fieldKey][$index][$subKey] ?? null;

                        if ($subField['type'] === 'image') {
                            if ($request->hasFile("content.{$fieldKey}.{$index}.{$subKey}")) {
                                if ($currentValue) {
                                    Storage::disk('public')->delete(Str::replaceFirst('storage/', '', $currentValue));
                                }

                                $filePath = $request->file("content.{$fieldKey}.{$index}.{$subKey}")
                                    ->store($uploadPath, 'public');

                                $repeaterItemData[$subKey] = "storage/{$filePath}";
                            } else {
                                $repeaterItemData[$subKey] = $item[$subKey . '_existing'] ?? $currentValue ?? null;
                            }

                            unset($repeaterItemData[$subKey . '_existing']);
                        } else {
                            $repeaterItemData[$subKey] = $item[$subKey] ?? null;
                        }
                    }

                    $contentToSave[$fieldKey][] = $repeaterItemData;
                }
            }

            // Handle single image fields
            elseif ($field['type'] === 'image') {
                $currentValue = $existingContent[$fieldKey] ?? null;

                if ($request->hasFile("content.{$fieldKey}")) {
                    if ($currentValue) {
                        Storage::disk('public')->delete(Str::replaceFirst('storage/', '', $currentValue));
                    }

                    $filePath = $request->file("content.{$fieldKey}")
                        ->store($uploadPath, 'public');

                    $contentToSave[$fieldKey] = "storage/{$filePath}";
                } else {
                    $contentToSave[$fieldKey] = $currentValue;
                }
            }

            // Handle text, textarea, etc.
            else {
                $contentToSave[$fieldKey] = $inputData[$fieldKey] ?? null;
            }
        }

        Setting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => $contentToSave]
        );

        return redirect()->route('admin.website.section.edit', $key)->with('success', 'Section updated successfully!');
    }
}
