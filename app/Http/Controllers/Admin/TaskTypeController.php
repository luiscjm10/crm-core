<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Projects\Actions\CreateTaskTypeAction;
use App\Domains\Projects\Actions\DeleteTaskTypeAction;
use App\Domains\Projects\Actions\UpdateTaskTypeAction;
use App\Domains\Projects\TaskType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:task-types.read')->only('index');
        $this->middleware('permission:task-types.create')->only('store');
        $this->middleware('permission:task-types.update')->only('update');
        $this->middleware('permission:task-types.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $taskTypes = TaskType::orderBy('name')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        return Inertia::render('Admin/TaskTypes/Index', [
            'taskTypes' => $taskTypes,
        ]);
    }

    public function store(Request $request, CreateTaskTypeAction $createTaskType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:task_types,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $createTaskType->execute($validated);

        return redirect()->route('admin.task-types.index');
    }

    public function update(Request $request, TaskType $taskType, UpdateTaskTypeAction $updateTaskType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:task_types,name,' . $taskType->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $updateTaskType->execute($taskType, $validated);

        return redirect()->route('admin.task-types.index');
    }

    public function destroy(TaskType $taskType, DeleteTaskTypeAction $deleteTaskType)
    {
        $deleteTaskType->execute($taskType);

        return redirect()->route('admin.task-types.index');
    }
}
