<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        Log::info('TaskController@index called');
        return response()->json(Task::all());
    }

    public function store(Request $request)
    {
        Log::info('TaskController@store called');

        $tasks = $request->all();

        foreach ($tasks as $taskData) {
            $validator = \Validator::make($taskData, [
                'project_id' => 'required|exists:projects,id',
                'title' => 'required',
                'description' => 'required',
                'status' => 'required|in:pending,complete',
                'due_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $task = Task::create($taskData);
            $createdTasks[] = $task;
        }

        return response()->json($createdTasks, 201);  // 201 Created
    }

    public function show(Task $task)
    {
        Log::info('TaskController@show called');
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        Log::info('TaskController@update called');

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required|in:pending,complete',
            'due_date' => 'required|date'
        ]);

        $task->update($request->all());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task
        ], 200); 
    }

    public function destroy(Task $task)
    {
        Log::info('TaskController@destroy called');
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
