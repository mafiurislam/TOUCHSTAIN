<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    /**
     * Marketing & Coupons Overview
     */
    public function coupons()
    {
        $coupons = Coupon::orderByDesc('id')->get();
        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Store new coupon
     */
    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'title' => 'required|string|max:255',
            'discount' => 'required|numeric|min:1',
            'discount_type' => 'required|in:percent,amount',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'expire_date' => 'nullable|date',
            'limit' => 'nullable|integer',
        ]);

        $coupon = Coupon::create([
            'title' => $validated['title'],
            'code' => strtoupper($validated['code']),
            'discount' => $validated['discount'],
            'discount_type' => $validated['discount_type'],
            'min_purchase' => $validated['min_purchase'] ?? 0,
            'max_discount' => $validated['max_discount'] ?? 0,
            'expire_date' => $validated['expire_date'] ?? date('Y-12-31'),
            'limit' => $validated['limit'] ?? 100,
            'status' => 1,
        ]);

        AuditLog::record('Marketing', 'Create Coupon', "Created coupon [{$coupon->code}]");

        return back()->with('success', "Coupon '{$coupon->code}' created successfully!");
    }

    /**
     * Delete Coupon
     */
    public function destroyCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $code = $coupon->code;
        $coupon->delete();

        AuditLog::record('Marketing', 'Delete Coupon', "Deleted coupon [{$code}]");

        return back()->with('success', "Coupon '{$code}' deleted successfully.");
    }
}
