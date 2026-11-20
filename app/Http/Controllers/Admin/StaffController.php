<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * List all staff and administrators
     */
    public function index()
    {
        $staffMembers = User::whereIn('role', ['admin', 'super_admin', 'manager', 'staff'])
            ->orderBy('id')
            ->get();

        return view('admin.staff.index', compact('staffMembers'));
    }

    /**
     * Create new staff user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'role' => 'required|in:admin,super_admin,manager,staff',
            'password' => 'required|string|min:6',
        ]);

        $staff = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'must_change_password' => true,
        ]);

        AuditLog::record('Staff', 'Create Staff', "Created staff member [{$staff->username}] with role {$staff->role}");

        return back()->with('success', "Staff member '{$staff->name}' created successfully!");
    }

    /**
     * View Audit & Security Activity Logs
     */
    public function auditLogs()
    {
        $logs = AuditLog::orderByDesc('id')->paginate(25);
        return view('admin.staff.audit-logs', compact('logs'));
    }
}
