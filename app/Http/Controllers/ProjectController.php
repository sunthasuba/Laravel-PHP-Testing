<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index()
    {
        Log::info('ProjectController@index called');
        return response()->json(Project::all());
    }

    public function store(Request $request)
    {
        Log::info('ProjectController@store called');

        $projects = $request->all();

        foreach ($projects as $projectData) {
            $validator = \Validator::make($projectData, [
                'name' => 'required',
                'description' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $project = Project::create($projectData);
            $createdProjects[] = $project;
        }

        return response()->json($createdProjects, 201);  // 201 Created
    }

    public function show(Project $project)
    {
        Log::info('ProjectController@show called');
        return response()->json($project);
    }

    public function update(Request $request, Project $project)
{
    Log::info('ProjectController@update called');
    
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date'
    ]);

    $project->update($request->all());

    return response()->json([
        'message' => 'Project updated successfully',
        'project' => $project
    ], 200); 
}

    public function destroy(Project $project)
    {
        Log::info('ProjectController@destroy called');
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
