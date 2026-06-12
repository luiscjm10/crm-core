<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Clients\Company;
use App\Domains\Projects\Task;
use App\Domains\Projects\Actions\CreateTaskAction;
use App\Domains\Projects\Actions\UpdateTaskAction;
use App\Domains\Projects\Actions\DeleteTaskAction;
use App\Domains\Projects\Actions\CompleteTaskAction;
use App\Domains\Projects\Enums\TaskPriority;
use App\Domains\Projects\Enums\TaskStatus;
use App\Domains\Projects\Enums\TaskType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;
        $companyId = $request->get('company_id');

        $tasks = Task::with('assignedUser', 'creator', 'company');

        if ($companyId && $companyId !== 'all') {
            $tasks->where('company_id', $companyId);
        }

        $tasks = $tasks->latest()->paginate($perPage)
            ->appends(['perPage' => $perPage, 'company_id' => $companyId]);

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'statuses' => TaskStatus::values(),
            'types' => TaskType::values(),
            'priorities' => TaskPriority::values(),
            'companies' => Company::select('id', 'name')->where('is_active', true)->get(),
            'currentCompanyId' => $companyId ?: 'all',
        ]);
    }

    public function create(Request $request, Company $company)
    {
        return Inertia::render('Admin/Tasks/Create', [
            'users' => User::all(['id', 'name']),
            'statuses' => TaskStatus::values(),
            'types' => TaskType::values(),
            'priorities' => TaskPriority::values(),
            'company' => $company,
        ]);
    }

    public function store(Request $request, Company $company, CreateTaskAction $createTask)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', TaskStatus::values()),
            'due_date' => 'nullable|date',
            'taskable_type' => 'nullable|string|max:255',
            'taskable_id' => 'nullable|integer',
            'assigned_user_id' => 'nullable|exists:users,id',
            'is_recurring' => 'nullable|boolean',
            'recurrence_interval' => 'nullable|string|in:daily,weekly,biweekly,monthly,quarterly,semi-annually,annually',
            'type' => 'nullable|string|in:' . implode(',', TaskType::values()),
            'priority' => 'nullable|string|in:' . implode(',', TaskPriority::values()),
        ]);

        $validated['company_id'] = $company->id;

        $createTask->execute($validated);

        return redirect()->route('admin.tasks.index', ['company_id' => $company->id]);
    }

    public function show(Request $request, Company $company, Task $task)
    {
        $task->load('assignedUser', 'creator', 'taskable');

        return Inertia::render('Admin/Tasks/Show', [
            'task' => $task,
            'company' => $company,
        ]);
    }

    public function edit(Request $request, Company $company, Task $task)
    {
        $task->load('assignedUser', 'creator');

        return Inertia::render('Admin/Tasks/Edit', [
            'task' => $task,
            'users' => User::all(['id', 'name']),
            'statuses' => TaskStatus::values(),
            'types' => TaskType::values(),
            'priorities' => TaskPriority::values(),
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company, Task $task, UpdateTaskAction $updateTask)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', TaskStatus::values()),
            'due_date' => 'nullable|date',
            'taskable_type' => 'nullable|string|max:255',
            'taskable_id' => 'nullable|integer',
            'assigned_user_id' => 'nullable|exists:users,id',
            'is_recurring' => 'nullable|boolean',
            'recurrence_interval' => 'nullable|string|in:daily,weekly,biweekly,monthly,quarterly,semi-annually,annually',
            'type' => 'nullable|string|in:' . implode(',', TaskType::values()),
            'priority' => 'nullable|string|in:' . implode(',', TaskPriority::values()),
        ]);

        $updateTask->execute($task, $validated);

        return redirect()->route('admin.tasks.index', ['company_id' => $company->id]);
    }

    public function destroy(Request $request, Company $company, Task $task, DeleteTaskAction $deleteTask)
    {
        $deleteTask->execute($task);

        return redirect()->route('admin.tasks.index', ['company_id' => $company->id]);
    }

    public function complete(Request $request, Company $company, Task $task, CompleteTaskAction $completeTask)
    {
        $completeTask->execute($task);

        return redirect()->route('admin.tasks.index', ['company_id' => $company->id]);
    }

    public function updateStatus(Request $request, Company $company, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', TaskStatus::values()),
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks.index', ['company_id' => $company->id]);
    }
}
