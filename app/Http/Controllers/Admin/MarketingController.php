<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        $code = strtoupper(trim((string)$request->input('code', '')));
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'title' => 'required|string|max:255',
            'discount' => 'required|numeric|min:0.01',
            'discount_type' => 'required|in:percent,amount',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'expire_date' => 'nullable|string',
            'limit' => 'nullable|integer',
        ], [
            'code.required' => 'Coupon code is required.',
            'code.unique' => "The coupon code '{$code}' already exists. Please choose a different code.",
            'title.required' => 'Coupon title is required.',
            'discount.required' => 'Discount value is required.',
            'discount.min' => 'Discount value must be greater than 0.',
        ]);

        $couponData = [
            'title' => trim($validated['title']),
            'code' => $code,
            'discount' => (float)$validated['discount'],
            'discount_type' => $validated['discount_type'],
            'min_purchase' => isset($validated['min_purchase']) ? (float)$validated['min_purchase'] : 0,
            'max_discount' => isset($validated['max_discount']) ? (float)$validated['max_discount'] : 0,
            'expire_date' => !empty($validated['expire_date']) ? $validated['expire_date'] : date('Y-12-31'),
            'status' => 1,
        ];

        // Safe check for limit column in coupons table so it never throws 500 error
        if (Schema::hasColumn('coupons', 'limit')) {
            $couponData['limit'] = isset($validated['limit']) ? (int)$validated['limit'] : 100;
        }

        $coupon = Coupon::create($couponData);

        AuditLog::record('Marketing', 'Create Coupon', "Created coupon [{$coupon->code}]");

        return back()->with('success', "Coupon '{$coupon->code}' created successfully!");
    }

    /**
     * Toggle Coupon Active/Inactive Status
     */
    public function toggleCouponStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->status = $coupon->status ? 0 : 1;
        $coupon->save();

        $statusText = $coupon->status ? 'activated' : 'deactivated';
        AuditLog::record('Marketing', 'Toggle Coupon', "Coupon [{$coupon->code}] {$statusText}");

        return back()->with('success', "Coupon '{$coupon->code}' has been {$statusText}!");
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
