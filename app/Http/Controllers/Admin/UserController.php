<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,operator',
        ]);

        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Log the user creation
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logUserManagement(auth()->id(), 'create', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,operator',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Prevent demoting oneself from admin role
        if ($user->id === auth()->id() && $user->role === 'admin' && $request->role !== 'admin') {
            return redirect()->back()
                             ->with('error', 'Anda tidak dapat menurunkan peran Anda sendiri dari admin.')
                             ->withInput();
        }

        // Prevent changing role if it's the last admin
        if ($request->role !== $user->role && $user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()
                                 ->with('error', 'Tidak dapat mengubah peran admin terakhir.')
                                 ->withInput();
            }
        }

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ];

        $data = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Log the user update
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logUserManagement(auth()->id(), 'update', $user->id, [
            'old' => $oldData,
            'new' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Prevent deletion of the last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')
                                 ->with('error', 'Tidak dapat menghapus admin terakhir.');
            }
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ];

        $user->delete();

        // Log the user deletion
        $monitoringService = new \App\Services\QueueMonitoringService();
        $monitoringService->logUserManagement(auth()->id(), 'delete', $user->id, $userData);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}
