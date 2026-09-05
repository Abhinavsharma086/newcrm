<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $stats = [
            'my_tasks' => Task::where('assigned_to', $userId)->whereIn('status', ['todo', 'progress'])->count(),
            'my_tickets' => Ticket::where('assigned_to', $userId)->whereIn('status', ['open', 'progress'])->count(),
            'my_leads' => Lead::where('assigned_to', $userId)->whereNotIn('stage', ['converted', 'lost'])->count(),
            'completed_tasks' => Task::where('assigned_to', $userId)->where('status', 'done')->count(),
        ];

        $recentTasks = Task::where('assigned_to', $userId)->latest()->take(5)->get();
        $recentTickets = Ticket::where('assigned_to', $userId)->with('customer')->latest()->take(5)->get();

        return view('employee.dashboard', compact('stats', 'recentTasks', 'recentTickets'));
    }
}
