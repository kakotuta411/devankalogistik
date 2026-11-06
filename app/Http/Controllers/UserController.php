<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['name','email','role','branch_id','created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'desc' ? 'desc' : 'asc') : 'desc';

        $query = User::with('branch');
        $query->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                    $q2->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%");
                }))
              ->when($request->role, fn($q, $r) => $q->where('role', $r))
              ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
              ->orderBy($sortCol, $sortDir);
        $users = $query->paginate(10);
        $branches = Branch::all();
        return view('users.index', compact('users', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        AuditService::log('Create User', 'CREATE', $user);
        return redirect()->route('users.index')->with('success', 'User dibuat');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        AuditService::log('Update User', 'UPDATE', $user);
        return redirect()->route('users.index')->with('success', 'User diperbarui');
    }

    public function destroy(User $user)
    {
        AuditService::log('Delete User', 'DELETE', $user);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User dihapus');
    }
}
