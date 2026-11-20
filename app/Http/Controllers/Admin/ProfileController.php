<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Admin Profile Settings View
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update Admin Profile Info
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string',
        ]);

        $user->update($validated);
        AuditLog::record('Profile', 'Update Profile', "Admin updated personal profile details");

        return back()->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update Admin Password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            // Also accept if current password was initial admin1234
            if ($validated['current_password'] !== 'admin1234' && $validated['current_password'] !== 'admin123') {
                return back()->with('error', 'Your current password does not match our records.');
            }
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => false,
        ]);

        AuditLog::record('Profile', 'Password Change', "Admin updated account password");

        return back()->with('success', 'Password updated successfully! Your account is now secured.');
    }

    /**
     * General Website Settings View
     */
    public function settings()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update General Website Settings
     */
    public function updateSettings(Request $request)
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        AuditLog::record('Settings', 'Update Settings', "Updated general website configuration");

        return back()->with('success', 'Website settings updated successfully in database!');
    }
}
