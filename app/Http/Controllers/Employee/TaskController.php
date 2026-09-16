<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('assigned_to', auth()->id())->with('creator')->latest();
        
        if ($request->filled('task_date')) {
            $query->whereDate('created_at', $request->task_date);
        }
        
        $tasks = $query->get();

        $tasksByStatus = [
            'todo' => $tasks->where('status', 'todo'),
            'progress' => $tasks->where('status', 'progress'),
            'review' => $tasks->where('status', 'review'),
            'done' => $tasks->where('status', 'done'),
        ];

        return view('employee.tasks.index', compact('tasks', 'tasksByStatus'));
    }

    public function show(Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $task->load('creator', 'comments.user', 'attachments');
        return view('employee.tasks.show', compact('task'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:todo,progress,review,done',
        ]);

        if ($validated['status'] === 'done') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return response()->json(['success' => true, 'message' => 'Task status updated']);
    }

    public function addComment(Request $request, Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comment added successfully');
    }
}
