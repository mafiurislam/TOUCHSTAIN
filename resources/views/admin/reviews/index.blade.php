@extends('admin.layouts.master')

@section('title', 'Customer Reviews & Ratings — TOUCHSTAINS Admin')

@section('content')

    <!-- KPI Metric Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:16px;margin-bottom:24px">
        <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px">Total Reviews</span>
                <span style="font-size:22px">💬</span>
            </div>
            <div style="font-size:26px;font-weight:800;color:var(--text-dark)">{{ number_format($totalReviews) }}</div>
            <span style="font-size:11.5px;color:#64748B;display:block;margin-top:4px">Across all store products</span>
        </div>

        <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px">Average Rating</span>
                <span style="font-size:22px">⭐</span>
            </div>
            <div style="font-size:26px;font-weight:800;color:#D97706">★ {{ number_format($avgRating, 1) }} <span style="font-size:14px;color:#94A3B8;font-weight:600">/ 5.0</span></div>
            <span style="font-size:11.5px;color:#059669;display:block;margin-top:4px">Overall store satisfaction</span>
        </div>

        <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px">5-Star Reviews</span>
                <span style="font-size:22px">💖</span>
            </div>
            <div style="font-size:26px;font-weight:800;color:#166534">{{ number_format($fiveStarCount) }}</div>
            <span style="font-size:11.5px;color:#166534;display:block;margin-top:4px">Excellent feedback</span>
        </div>

        <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px">Bad / Low Reviews</span>
                <span style="font-size:22px">⚠️</span>
            </div>
            <div style="font-size:26px;font-weight:800;color:#DC2626">{{ number_format($badReviewCount) }}</div>
            <span style="font-size:11.5px;color:#DC2626;display:block;margin-top:4px">1 &amp; 2 Star ratings</span>
        </div>
    </div>

    <!-- Main Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">⭐ Customer Reviews &amp; Ratings</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">
                    কাস্টমারদের দেওয়া রিভিউ ও রেটিং পর্যবেক্ষণ করুন এবং প্রয়োজনমতো অপ্রীতিকর বা খারাপ রিভিউ ডিলিট করুন
                </p>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <span class="status-badge status-info" style="font-size:12px;padding:6px 12px">
                    Showing {{ $reviews->count() }} of {{ $reviews->total() }} Reviews
                </span>
            </div>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.reviews') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search reviewer name, product, or comment..."
                class="search-input"
                style="flex:1.5;min-width:220px;padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff"
            >

            <!-- Star Filter -->
            <select name="rating" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
                <option value="">All Ratings (সমস্ত রেটিং)</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>★★★★★ 5 Stars Only</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>★★★★☆ 4 Stars Only</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>★★★☆☆ 3 Stars Only</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>★★☆☆☆ 2 Stars Only (Low)</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>★☆☆☆☆ 1 Star Only (Bad)</option>
            </select>

            <!-- Product Filter -->
            <select name="product_id" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff;max-width:240px">
                <option value="">All Products (সমস্ত প্রোডাক্ট)</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ Str::limit($p->name, 28) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-theme" style="padding:9px 18px">🔍 Filter</button>
            <a href="{{ route('admin.reviews') }}" class="btn-secondary" style="padding:9px 16px">Reset</a>
        </form>

        <!-- Reviews Table -->
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:240px">Product</th>
                        <th style="width:180px">Customer / Reviewer</th>
                        <th style="width:140px">Rating</th>
                        <th>Review Comment</th>
                        <th style="width:140px">Date &amp; Time</th>
                        <th style="width:100px;text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rev)
                        <tr>
                            <td style="font-weight:700;color:#64748B">#{{ $rev->id }}</td>

                            <!-- Product Info -->
                            <td>
                                @if($rev->product)
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <img
                                            src="{{ $rev->product->image_url }}"
                                            alt="{{ $rev->product->name }}"
                                            style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0"
                                            onerror="this.src='{{ asset('assets/image/placeholder.png') }}'"
                                        >
                                        <div style="min-width:0">
                                            <a
                                                href="{{ url('/product/' . $rev->product->id) }}"
                                                target="_blank"
                                                style="font-size:13px;font-weight:700;color:var(--text-dark);text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                                title="{{ $rev->product->name }}"
                                            >
                                                {{ $rev->product->name }}
                                            </a>
                                            <a
                                                href="{{ route('admin.products.edit', $rev->product->id) }}"
                                                style="font-size:11.5px;color:var(--primary);text-decoration:none"
                                            >
                                                Edit Product ↗
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <span style="color:#94A3B8;font-size:12.5px;font-style:italic">Deleted Product</span>
                                @endif
                            </td>

                            <!-- Customer / Reviewer -->
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span style="width:30px;height:30px;border-radius:50%;background:#EFF6FF;color:#2563EB;font-weight:800;font-size:12px;display:flex;align-items:center;justify-content:center;border:1px solid #BFDBFE;flex-shrink:0">
                                        {{ strtoupper(substr($rev->reviewer_name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <strong style="font-size:13px;color:#0F172A;display:block">{{ $rev->reviewer_name }}</strong>
                                        <span style="font-size:11px;font-weight:700;color:#059669;background:#DCFCE7;padding:1px 6px;border-radius:4px;border:1px solid #BBF7D0">
                                            ✓ Verified
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Star Rating -->
                            <td>
                                <div>
                                    <span style="color:#F59E0B;font-size:14px;letter-spacing:1px">
                                        {{ str_repeat('★', (int)$rev->rating) }}{{ str_repeat('☆', max(0, 5 - (int)$rev->rating)) }}
                                    </span>
                                    <div style="font-size:11.5px;font-weight:700;color:{{ $rev->rating <= 2 ? '#EF4444' : ($rev->rating == 3 ? '#F59E0B' : '#059669') }};margin-top:2px">
                                        {{ $rev->rating }} / 5 Stars
                                        @if($rev->rating <= 2)
                                            <span style="background:#FEE2E2;color:#991B1B;padding:1px 5px;border-radius:3px;font-size:10px;margin-left:3px">Low</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Comment -->
                            <td>
                                <p style="font-size:13px;color:#334155;margin:0;line-height:1.45;word-break:break-word">
                                    {{ $rev->comment }}
                                </p>
                            </td>

                            <!-- Date -->
                            <td style="font-size:12px;color:#64748B">
                                {{ $rev->created_at ? $rev->created_at->format('d M, Y') : 'N/A' }}
                                <div style="font-size:11px;color:#94A3B8">
                                    {{ $rev->created_at ? $rev->created_at->format('h:i A') : '' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td style="text-align:right">
                                <form
                                    action="{{ route('admin.reviews.delete', $rev->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this review from \'{{ addslashes($rev->reviewer_name) }}\'? This action cannot be undone.')"
                                    style="display:inline-block;margin:0"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn-secondary"
                                        style="padding:6px 12px;font-size:12px;color:#EF4444;border-color:#FCA5A5;background:#FEF2F2"
                                        title="Delete this review"
                                    >
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px 20px;color:#94A3B8">
                                <div style="font-size:36px;margin-bottom:8px">⭐</div>
                                <strong style="font-size:14.5px;color:#475569;display:block;margin-bottom:4px">No customer reviews found</strong>
                                <p style="font-size:12.5px;color:#94A3B8;margin:0">
                                    কাস্টমাররা যখন প্রোডাক্ট পেজে রিভিউ ও রেটিং দেবে, সেগুলো এখানে শো করবে এবং আপনি পরিচালনা করতে পারবেন।
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div style="margin-top:20px;padding:14px;background:#F8FAFC;border-radius:10px;border:1px solid var(--border)">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

@endsection
