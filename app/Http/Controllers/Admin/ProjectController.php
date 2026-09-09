<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function projectList()
    {
        goIfUserCan('view-projects');
        
        $title = 'Projects';
        
        $projects = Project::latest()->searching(['title', 'details'])->paginate(10);
        
        return view('admin.project.list', compact('title', 'projects'));
    }

    public function createProject()
    {
        goIfUserCan('save-projects');

        $title = __('Add New Project');

        return view('admin.project.form', compact('title'));
    }

    public function saveProject(Request $request, $id = null)
    {
        goIfUserCan('save-projects');
        
        $request->validate([
            'title'   => 'required|string|max:255|unique:projects,title,' . $id,
            'image'   => $id ? 'nullable|image|max:4096' : 'nullable|image|max:4096',
            'details' => 'nullable|string',
            'date'    => $id ? 'nullable|date' : 'required|date', // optional date input
        ]);
    
        $project = $id ? Project::findOrFail($id) : new Project();
    
        $project->admin_id = admin()->id;
        $project->title    = $request->title;
        $project->details  = $request->details;
    
        // If you have a date input for created_at
        if ($request->filled('date')) {
            $project->created_at = $request->date;
        }
    
        if ($request->hasFile('image')) {
            $project->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('project'),
                $project->image ?? null,
                handleResize('project')
            );
        }
    
        $project->save();
    
        return redirect()->route('admin.project.list')
            ->with('success', $id ? 'Project updated successfully.' : 'Project created successfully.');
    }


    public function editProject($id)
    {
        goIfUserCan('save-projects');
        
        $title = __('Edit Project');
        $project = Project::findOrFail($id);

        return view('admin.project.form', compact('title', 'project'));
    }

    public function deleteProject($id)
    {
        goIfUserCan('delete-projects');
        
        $project = Project::findOrFail($id);

        if ($project->image && Storage::disk('public')->exists($project->image)) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return redirect()->route('admin.project.list')->with('success', 'Project deleted successfully.');
    }
}
