<?php

namespace App\Livewire\Project;

use App\Models\Project;
use Livewire\Component;

class ProjectList extends Component
{
    public function render()
    {
        $title = 'Projects';

        $projects = Project::latest()->paginate(10);

        return view('admin.project.list', compact('title', 'projects'));
        // return view('livewire.project.project-list');
    }
}
