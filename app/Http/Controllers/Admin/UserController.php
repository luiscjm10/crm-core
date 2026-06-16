<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Actions\UpdateUserAction;
use App\Domains\Auth\Actions\DeleteUserAction;
use App\Domains\Clients\Company;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $users = User::with('roles', 'company')->orderBy('id')->paginate($perPage)->appends(['perPage' => $perPage]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function store(Request $request, CreateUserAction $createUser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'nullable|exists:roles,name',
        ]);

        $createUser->execute($validated, $validated['role'] ?? null);

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        $user->load('roles', 'company');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::all(),
            'companies' => Company::all(),
        ]);
    }

    public function update(Request $request, User $user, UpdateUserAction $updateUser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'nullable|exists:roles,name',
        ]);

        $updateUser->execute($user, $validated, $validated['role'] ?? null);

        return redirect()->route('admin.users.index');
    }

    public function destroy(Request $request, User $user, DeleteUserAction $deleteUser)
    {
        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $deleteUser->execute($user);

        return redirect()->route('admin.users.index');
    }
}
