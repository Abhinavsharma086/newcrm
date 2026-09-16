<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['assignee', 'creator', 'customer'])->latest();

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('task_date')) {
            $query->whereDate('created_at', $request->task_date);
        }

        $tasks = $query->get();
        $employees = User::where('status', 'active')->get();
        $customers = Customer::select('id', 'name', 'phone', 'gstin', 'city', 'society')->latest()->get();

        $tasksByStatus = [
            'todo' => $tasks->where('status', 'todo'),
            'progress' => $tasks->where('status', 'progress'),
            'review' => $tasks->where('status', 'review'),
            'done' => $tasks->where('status', 'done'),
        ];
        
        return view('admin.tasks.index', compact('tasks', 'tasksByStatus', 'employees', 'customers'));
    }

    public function create()
    {
        $employees = User::where('status', 'active')->get();
        $customers = Customer::select('id', 'name', 'phone', 'gstin', 'city', 'society')->latest()->get();
        return view('admin.tasks.create', compact('employees', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,progress,review,done',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'checklist_items' => 'nullable|array',
            'checklist_items.*' => 'nullable|string',
        ]);

        $checklist = [];
        if (!empty($validated['checklist_items'])) {
            foreach ($validated['checklist_items'] as $item) {
                if (trim($item) !== '') {
                    $checklist[] = [
                        'id' => uniqid(),
                        'text' => trim($item),
                        'completed' => false
                    ];
                }
            }
        }

        $taskData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'checklist' => $checklist,
            'created_by' => auth()->id(),
        ];

        if ($taskData['status'] === 'done') {
            $taskData['completed_at'] = now();
        }

        Task::create($taskData);

        return redirect()->route('admin.tasks.index')->with('success', 'Task created and assigned successfully.');
    }

    public function show(Task $task)
    {
        $task->load([
            'assignee',
            'creator',
            'comments.user',
            'attachments',
            'customer.invoices' => function ($q) {
                $q->latest();
            },
            'customer.quotations' => function ($q) {
                $q->latest();
            },
            'customer.appointments' => function ($q) {
                $q->latest();
            }
        ]);

        $employees = User::where('status', 'active')->get();

        return view('admin.tasks.show', compact('task', 'employees'));
    }

    public function edit(Task $task)
    {
        $employees = User::where('status', 'active')->get();
        $customers = Customer::select('id', 'name', 'phone', 'gstin', 'city', 'society')->latest()->get();
        return view('admin.tasks.edit', compact('task', 'employees', 'customers'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,progress,review,done',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.text' => 'nullable|string',
            'checklist_items.*.completed' => 'nullable',
        ]);

        $checklist = [];
        if (!empty($validated['checklist_items'])) {
            foreach ($validated['checklist_items'] as $item) {
                if (!empty($item['text']) && trim($item['text']) !== '') {
                    $checklist[] = [
                        'id' => $item['id'] ?? uniqid(),
                        'text' => trim($item['text']),
                        'completed' => !empty($item['completed'])
                    ];
                }
            }
        }

        $taskData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'checklist' => $checklist,
        ];

        if ($validated['status'] === 'done' && $task->status !== 'done') {
            $taskData['completed_at'] = now();
        } elseif ($validated['status'] !== 'done') {
            $taskData['completed_at'] = null;
        }

        $task->update($taskData);

        return redirect()->route('admin.tasks.show', $task)->with('success', 'Task details updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,progress,review,done',
        ]);

        $taskData = ['status' => $validated['status']];
        if ($validated['status'] === 'done') {
            $taskData['completed_at'] = now();
        } else {
            $taskData['completed_at'] = null;
        }

        $task->update($taskData);

        return response()->json(['success' => true, 'message' => 'Task status updated.']);
    }

    public function quickAssign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update(['assigned_to' => $validated['assigned_to']]);

        $assigneeName = $task->assignee ? $task->assignee->name : 'Unassigned';
        return response()->json([
            'success' => true,
            'message' => "Task reassigned to {$assigneeName}.",
            'assignee_name' => $assigneeName
        ]);
    }

    public function toggleChecklist(Request $request, Task $task)
    {
        $validated = $request->validate([
            'item_id' => 'required|string',
            'completed' => 'required|boolean',
        ]);

        $checklist = $task->checklist ?? [];
        foreach ($checklist as &$item) {
            if ($item['id'] === $validated['item_id']) {
                $item['completed'] = $validated['completed'];
                break;
            }
        }

        $task->checklist = $checklist;
        $task->save();

        return response()->json(['success' => true, 'message' => 'Checklist item updated.']);
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('admin.tasks.show', $task)->with('success', 'Comment added successfully.');
    }
}

