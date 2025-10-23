<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->latest()->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully.');
    }

    /**
     * Approve user registration
     */
    public function approve(User $user)
    {
        if ($user->isApproved()) {
            return back()->with('error', 'User is already approved.');
        }

        $user->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
        ]);

        // TODO: Send email notification to user
        
        return back()->with('success', 'User approved successfully.');
    }

    /**
     * Reject user registration
     */
    public function reject(User $user)
    {
        if ($user->isRejected()) {
            return back()->with('error', 'User is already rejected.');
        }

        $user->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // TODO: Send email notification to user
        
        return back()->with('success', 'User rejected.');
    }

    /**
     * Show pending users for approval
     */
    public function pending()
    {
        $users = User::where('status', 'pending')->latest()->paginate(10);
        return view('admin.users.pending', compact('users'));
    }

    /**
     * Search users for AJAX requests (used in notifications)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->limit(20)
                    ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
