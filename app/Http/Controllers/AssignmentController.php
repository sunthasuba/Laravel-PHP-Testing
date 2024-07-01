<?php

namespace App\Http\Controllers;

use App\Models\Assignment; // Ensure this line is correct
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function index()
    {
        Log::info('AssignmentController@index called');
        $assignments = Assignment::all();
        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        Log::info('AssignmentController@store called');

        $assignmentsData = $request->all();
        if (isset($assignmentsData['task_id'])) {
            // Single assignment provided as object
            $assignmentsData = [$assignmentsData];
        } elseif (!is_array($assignmentsData)) {
            Log::error('Invalid data format', ['data' => $assignmentsData]);
            return response()->json([
                'error' => 'Invalid data format.'
            ], 400);
        }

        $createdAssignments = [];

        foreach ($assignmentsData as $assignmentData) {
            // Check if user is already assigned to a task in the same project
            $task = Task::findOrFail($assignmentData['task_id']);
            $project = $task->project;

            $existingAssignment = Assignment::where('user_id', $assignmentData['user_id'])
                ->whereHas('task', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->first();

            if ($existingAssignment) {
                Log::error('User already assigned', ['user_id' => $assignmentData['user_id'], 'project_id' => $project->id]);
                return response()->json([
                    'error' => 'User is already assigned to a task in this project.'
                ], 400);
            }

            // Validate and create assignment
            $validator = Validator::make($assignmentData, [
                'task_id' => 'required|exists:tasks,id',
                'user_id' => 'required|exists:users,id',
                'hours_worked' => 'required|integer',
                'assigned_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['error' => $validator->errors()], 400);
            }

            try {
                $assignment = Assignment::create($assignmentData);
                $createdAssignments[] = $assignment;
                Log::info('Assignment created', ['assignment' => $assignment]);
            } catch (\Exception $e) {
                Log::error('Assignment creation failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to create assignment'], 500);
            }
        }

        return response()->json([
            'message' => 'Assignments created successfully',
            'assignments' => $createdAssignments
        ], 201);
    }

    public function show(Assignment $assignment)
    {
        Log::info('AssignmentController@show called');
        return response()->json($assignment);
    }

    public function update(Request $request, Assignment $assignment)
    {
        Log::info('AssignmentController@update called');

        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'hours_worked' => 'required|integer',
            'assigned_date' => 'required|date'
        ]);

        $assignment->update($request->all());

        return response()->json([
            'message' => 'Assignment updated successfully',
            'assignment' => $assignment
        ], 200);
    }

    public function destroy(Assignment $assignment)
    {
        Log::info('AssignmentController@destroy called');
        $assignment->delete();
        return response()->json(['message' => 'Assignment deleted successfully'], 200);
    }
}