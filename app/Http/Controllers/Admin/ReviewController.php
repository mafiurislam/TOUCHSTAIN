<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display all customer reviews with filtering & analytics
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', (int)$request->input('rating'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int)$request->input('product_id'));
        }

        if ($request->filled('search')) {
            $s = '%' . trim((string)$request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', $s)
                  ->orWhere('comment', 'like', $s)
                  ->orWhereHas('product', function ($pq) use ($s) {
                      $pq->where('name', 'like', $s);
                  });
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        // Analytics KPI
        $totalReviews = Review::count();
        $avgRating = $totalReviews > 0 ? round((float)Review::avg('rating'), 1) : 5.0;
        $fiveStarCount = Review::where('rating', 5)->count();
        $badReviewCount = Review::whereIn('rating', [1, 2])->count();
        $products = Product::select('id', 'name')->orderBy('name')->get();

        return view('admin.reviews.index', compact(
            'reviews',
            'totalReviews',
            'avgRating',
            'fiveStarCount',
            'badReviewCount',
            'products'
        ));
    }

    /**
     * Delete an inappropriate / bad review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $productId = $review->product_id;
        $reviewerName = $review->reviewer_name;
        $rating = $review->rating;

        $review->delete();

        try {
            AuditLog::record('Reviews', 'Delete Review', "Deleted review [#{$id}] by {$reviewerName} (Rating: {$rating}★) for Product #{$productId}");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Review from '{$reviewerName}' has been deleted successfully!");
    }
}
