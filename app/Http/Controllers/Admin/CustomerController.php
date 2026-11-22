<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display listing of registered customers
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')->withCount('orders')->orderByDesc('id');

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->paginate(20)->withQueryString();

        // Calculate total spend per customer
        foreach ($customers as $c) {
            $c->total_spent = (float) $c->orders()->whereNotIn('order_status', ['canceled', 'unconfirmed'])->sum('order_amount');
            $c->is_vip = $c->total_spent >= 2500 || $c->orders_count >= 5;
        }

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        $customer = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? ($validated['phone'] . '@touchstains.com'),
            'password' => Hash::make($validated['password'] ?? '12345678'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        AuditLog::record('Customers', 'Create Customer', "Created customer [#{$customer->id}] {$customer->name}");

        return back()->with('success', "Customer '{$customer->name}' created successfully!");
    }

    /**
     * Update customer profile
     */
    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $customer->id,
            'email' => 'nullable|email|unique:users,email,' . $customer->id,
            'status' => 'required|in:active,blocked',
        ]);

        $customer->update($validated);
        AuditLog::record('Customers', 'Update Customer', "Updated customer [#{$customer->id}] {$customer->name}");

        return back()->with('success', "Customer '{$customer->name}' updated successfully!");
    }

    /**
     * Toggle Customer Block/Active Status
     */
    public function toggleStatus($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $newStatus = $customer->status === 'active' ? 'blocked' : 'active';
        $customer->update(['status' => $newStatus]);

        AuditLog::record('Customers', 'Status Toggle', "Toggled status of customer [#{$customer->id}] to {$newStatus}");

        return back()->with('success', "Customer status changed to " . strtoupper($newStatus));
    }

    /**
     * Delete Customer Safely
     */
    public function destroy($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $name = $customer->name;
        $customer->delete();

        AuditLog::record('Customers', 'Delete Customer', "Deleted customer [#{$id}] {$name}");

        return back()->with('success', "Customer '{$name}' deleted successfully.");
    }

    /**
     * Export Customer List to CSV
     */
    public function exportCsv()
    {
        $customers = User::where('role', 'customer')->withCount('orders')->get();
        $filename = "customers_export_" . date('Y_m_d_His') . ".csv";

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'Phone', 'Email', 'Total Orders', 'Total Spent (INR)', 'Status', 'Registered Date']);

        foreach ($customers as $c) {
            $spent = (float) $c->orders()->whereNotIn('order_status', ['canceled', 'unconfirmed'])->sum('order_amount');
            fputcsv($handle, [
                $c->id,
                $c->name,
                $c->phone,
                $c->email,
                $c->orders_count,
                $spent,
                ucfirst($c->status ?? 'active'),
                $c->created_at ? $c->created_at->format('Y-m-d') : 'N/A',
            ]);
        }

        fseek($handle, 0);
        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
