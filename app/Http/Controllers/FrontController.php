<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setting;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\Message;
use App\Models\Newsletter;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\BankTransferLog;
use App\Models\AuditLog;
use App\Models\DemoVideo;
use App\Models\InventoryLog;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FrontController extends Controller
{
    /**
     * Ensure extra address columns (road, house, floor, is_default) exist on the addresses table.
     */
    protected function ensureAddressColumns(): void
    {
        try {
            if (!Schema::hasColumn('addresses', 'is_default')) {
                Schema::table('addresses', function ($table) {
                    if (!Schema::hasColumn('addresses', 'road')) {
                        $table->string('road')->nullable();
                    }
                    if (!Schema::hasColumn('addresses', 'house')) {
                        $table->string('house')->nullable();
                    }
                    if (!Schema::hasColumn('addresses', 'floor')) {
                        $table->string('floor')->nullable();
                    }
                    if (!Schema::hasColumn('addresses', 'is_default')) {
                        $table->boolean('is_default')->default(true);
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Address columns check: ' . $e->getMessage());
        }
    }
    /**
     * Automatically restore persistent customer login from long-lived cookie until explicit logout.
     */
    protected function restorePersistentCustomerLogin(): void
    {
        if (Auth::check()) {
            return;
        }
        try {
            if (request()->query('logged_out') === '1') {
                return;
            }
            $savedUid = Cookie::get('touchstains_auth_uid');
            if ($savedUid && is_numeric($savedUid)) {
                $persistedUser = User::find((int)$savedUid);
                if ($persistedUser && ($persistedUser->role ?? 'customer') !== 'admin') {
                    Auth::login($persistedUser, true);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Persistent login restore skipped: ' . $e->getMessage());
        }
    }

    /**
     * Get the current authenticated user or default demo customer from SQLite.
     */
    protected function currentUser(): User
    {
        $this->restorePersistentCustomerLogin();
        if (Auth::check()) {
            return Auth::user();
        }
        $user = User::where('role', 'customer')->first();
        if (!$user) {
            $user = User::first();
        }
        return $user;
    }

    /**
     * Build shared SQLite data for Blade views & client-side hydration.
     */
    protected function getSharedData(): array
    {
        $this->restorePersistentCustomerLogin();
        $user = $this->currentUser();
        $parentCategories = Category::where('parent_id', 0)
            ->where('status', 1)
            ->with(['children' => function ($q) {
                $q->where('status', 1)->orderBy('priority');
            }])
            ->orderBy('priority')
            ->get();

        $allProducts = Product::where('status', 1)->get();

        // Compute product count per parent and child category
        $catCounts = [];
        foreach ($allProducts as $prod) {
            $cIds = is_array($prod->category_ids) ? $prod->category_ids : [];
            foreach ($cIds as $cidObj) {
                $cid = isset($cidObj['id']) ? (int)$cidObj['id'] : 0;
                if ($cid > 0) {
                    $catCounts[$cid] = ($catCounts[$cid] ?? 0) + 1;
                }
            }
        }

        foreach ($parentCategories as $cat) {
            $cat->products_count = $catCounts[$cat->id] ?? 0;
            foreach ($cat->children as $child) {
                $child->products_count = $catCounts[$child->id] ?? 0;
            }
        }

        Banner::ensureMediaColumns();
        $banners = Banner::where('status', 1)->get();
        $coupons = Coupon::where('status', 1)->get();
        $branches = Branch::where('status', 1)->get();
        $isCustomerRegistered = Auth::check();
        $wishlistIds = $isCustomerRegistered
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
            : [];
        $registeredAddress = null;
        if ($isCustomerRegistered) {
            $this->ensureAddressColumns();
            $registeredAddress = Address::where('user_id', Auth::id())
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->first();
        }

        $settingsMap = Setting::pluck('value', 'key')->toArray();

        return [
            'currentUser' => $user,
            'isCustomerRegistered' => $isCustomerRegistered,
            'registeredUser' => $isCustomerRegistered ? Auth::user() : null,
            'registeredAddress' => $registeredAddress,
            'parentCategories' => $parentCategories,
            'allProducts' => $allProducts,
            'banners' => $banners,
            'coupons' => $coupons,
            'branches' => $branches,
            'wishlistIds' => $wishlistIds,
            'settings' => $settingsMap,
            'catCounts' => $catCounts,
        ];
    }

    public function home()
    {
        $shared = $this->getSharedData();
        $allById = $shared['allProducts']->keyBy('id');

        // Load exact API ordering from full_site_data.json if present, backed by SQLite Product models
        $jsonPath = database_path('seeders/full_site_data.json');
        $raw = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

        $mapIdsToModels = function (array $items, $fallbackQuery) use ($allById) {
            $models = collect();
            foreach ($items as $item) {
                $pid = $item['id'] ?? null;
                if ($pid && $allById->has($pid)) {
                    $models->push($allById->get($pid));
                }
            }
            return $models->count() > 0 ? $models : $fallbackQuery();
        };

        $dailyNeeds = $mapIdsToModels(
            $raw['dailyNeedsProducts'] ?? ($raw['daily_needs']['products'] ?? []),
            fn() => Product::where('status', 1)->where('daily_needs', 1)->orderByDesc('id')->take(15)->get()
        )->take(15);

        $featuredProducts = $mapIdsToModels(
            $raw['featuredProducts'] ?? ($raw['featured']['products'] ?? []),
            fn() => Product::where('status', 1)->where('is_featured', 1)->orderByDesc('id')->take(15)->get()
        )->take(15);

        $mostReviewed = $mapIdsToModels(
            $raw['mostReviewedProducts'] ?? ($raw['most_reviewed']['products'] ?? []),
            fn() => Product::where('status', 1)->orderByDesc('popularity_count')->take(15)->get()
        )->take(15);

        DemoVideo::ensureTableAndSeed();
        $demoVideos = DemoVideo::where('status', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.home', array_merge($shared, [
            'pageId' => 'home',
            'pageTitle' => 'TOUCHSTAINS',
            'dailyNeeds' => $dailyNeeds,
            'featuredProducts' => $featuredProducts,
            'mostReviewed' => $mostReviewed,
            'demoVideos' => $demoVideos,
        ]));
    }

    public function categories(Request $request)
    {
        $shared = $this->getSharedData();
        $selectedCatId = (int)$request->query('cat', $shared['parentCategories']->first()->id ?? 20);
        $selectedSubId = $request->query('sub', 'all');

        $selectedCategory = $shared['parentCategories']->firstWhere('id', $selectedCatId)
            ?? $shared['parentCategories']->first();

        $targetId = ($selectedSubId !== 'all' && is_numeric($selectedSubId))
            ? (int)$selectedSubId
            : ($selectedCategory ? $selectedCategory->id : 20);

        $categoryProducts = $shared['allProducts']->filter(function ($p) use ($targetId) {
            $cIds = is_array($p->category_ids) ? $p->category_ids : [];
            foreach ($cIds as $c) {
                if ((int)($c['id'] ?? 0) === $targetId) {
                    return true;
                }
            }
            return false;
        })->values();

        return view('pages.categories', array_merge($shared, [
            'pageId' => 'categories',
            'pageTitle' => ($selectedCategory ? $selectedCategory->name : 'Categories') . ' — TOUCHSTAINS',
            'selectedCategory' => $selectedCategory,
            'selectedCatId' => $selectedCategory ? $selectedCategory->id : 20,
            'selectedSubId' => $selectedSubId,
            'categoryProducts' => $categoryProducts,
        ]));
    }

    public function shop(Request $request)
    {
        $shared = $this->getSharedData();
        $products = $shared['allProducts'];

        $type = $request->query('type', '');
        $catId = $request->query('cat', '');
        $subId = $request->query('sub', '');
        $search = trim($request->query('search', $request->query('q', '')));
        $sort = $request->query('sort', 'default');

        if ($type === 'daily_needs') {
            $products = $products->filter(fn($p) => $p->daily_needs == 1 || $p->discount > 0);
        } elseif ($type === 'featured') {
            $products = $products->filter(fn($p) => $p->is_featured == 1);
        } elseif ($type === 'most_reviewed') {
            $products = $products->sortByDesc('popularity_count');
        }

        $filterCatId = $subId ?: $catId;
        if ($filterCatId) {
            $products = $products->filter(function ($p) use ($filterCatId) {
                $cIds = is_array($p->category_ids) ? $p->category_ids : [];
                foreach ($cIds as $c) {
                    if ((string)($c['id'] ?? '') === (string)$filterCatId) {
                        return true;
                    }
                }
                return false;
            });
        }

        if ($search !== '') {
            $qLower = mb_strtolower($search);
            $products = $products->filter(function ($p) use ($qLower) {
                return str_contains(mb_strtolower($p->name), $qLower)
                    || str_contains(mb_strtolower($p->description ?? ''), $qLower);
            });
        }

        return view('pages.shop', array_merge($shared, [
            'pageId' => 'shop',
            'pageTitle' => 'Shop All Products — TOUCHSTAINS',
            'filteredProducts' => $products->values(),
            'filterType' => $type,
            'filterCat' => $catId,
            'filterSub' => $subId,
            'searchQuery' => $search,
            'sortBy' => $sort,
        ]));
    }

    public function flashDeals(Request $request)
    {
        $request->merge(['type' => 'daily_needs']);
        return $this->shop($request);
    }

    public function product(Request $request, $id = null)
    {
        $shared = $this->getSharedData();
        $productId = $id ?: $request->query('id', 351);
        $product = Product::find($productId) ?? Product::first();

        if ($product) {
            $product->increment('view_count');
        }

        $cIds = is_array($product->category_ids) ? $product->category_ids : [];
        $primaryCatId = isset($cIds[0]['id']) ? (int)$cIds[0]['id'] : 20;

        $relatedProducts = $shared['allProducts']->filter(function ($p) use ($primaryCatId, $product) {
            if ($p->id === $product->id) return false;
            $ids = is_array($p->category_ids) ? $p->category_ids : [];
            foreach ($ids as $c) {
                if ((int)($c['id'] ?? 0) === $primaryCatId) return true;
            }
            return false;
        })->take(10)->values();

        $reviews = Review::where('product_id', $product->id)->with('user')->latest()->get();

        return view('pages.product', array_merge($shared, [
            'pageId' => 'product',
            'pageTitle' => ($product ? $product->name : 'Product Details') . ' — TOUCHSTAINS',
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
        ]));
    }

    public function cart()
    {
        $shared = $this->getSharedData();
        return view('pages.cart', array_merge($shared, [
            'pageId' => 'cart',
            'pageTitle' => 'Shopping Bag — TOUCHSTAINS',
        ]));
    }

    public function checkout()
    {
        $this->ensureAddressColumns();
        $shared = $this->getSharedData();
        $isCustomerRegistered = Auth::check();
        $authUser = $isCustomerRegistered ? Auth::user() : null;
        $registeredAddress = $isCustomerRegistered
            ? Address::where('user_id', $authUser->id)->orderByDesc('is_default')->orderByDesc('id')->first()
            : null;
        $addresses = $isCustomerRegistered
            ? Address::where('user_id', $authUser->id)->orderByDesc('is_default')->orderByDesc('id')->get()
            : collect();

        $razorpayKey = !empty($shared['settings']['razorpay_key_id'])
            ? $shared['settings']['razorpay_key_id']
            : config('services.razorpay.key', env('RAZORPAY_KEY', 'rzp_test_U1cQQReArMfXP9'));

        $isRazorpayActive = ($shared['settings']['payment_razorpay_active'] ?? '1') !== '0';
        $isCodActive = ($shared['settings']['payment_cod_active'] ?? '1') !== '0';
        $isUpiActive = ($shared['settings']['payment_upi_active'] ?? '1') !== '0';
        $upiId = $shared['settings']['payment_upi_id'] ?? Setting::getValue('payment_upi_id', '7039214465-3@ybl');
        $upiName = $shared['settings']['payment_upi_name'] ?? Setting::getValue('payment_upi_name', 'Naushad Ali Meher Ali');
        $upiQrImage = $shared['settings']['payment_upi_qr_image'] ?? Setting::getValue('payment_upi_qr_image', 'payment/upi_phonepe_qr.jpg');
        $defaultPayment = $isUpiActive ? 'upi' : ($isRazorpayActive ? 'razorpay' : ($isCodActive ? 'cash_on_delivery' : 'none'));

        return view('pages.checkout', array_merge($shared, [
            'pageId' => 'checkout',
            'pageTitle' => 'Checkout — TOUCHSTAINS',
            'addresses' => $addresses,
            'isCustomerRegistered' => $isCustomerRegistered,
            'registeredUser' => $authUser,
            'registeredAddress' => $registeredAddress,
            'razorpayKey' => $razorpayKey,
            'razorpayKeyId' => $razorpayKey,
            'isRazorpayActive' => $isRazorpayActive,
            'isCodActive' => $isCodActive,
            'isUpiActive' => $isUpiActive,
            'upiId' => $upiId,
            'upiName' => $upiName,
            'upiQrImage' => $upiQrImage,
            'defaultPayment' => $defaultPayment,
        ]));
    }

    /**
     * Create Order and Initialize Razorpay Payment
     */
    public function createRazorpayOrder(Request $request, RazorpayService $razorpayService)
    {
        if (Setting::getValue('payment_razorpay_active', '1') === '0') {
            return response()->json([
                'status' => false,
                'message' => 'Online Payment (Razorpay) is currently disabled by store admin. Please choose Cash on Delivery.',
            ], 422);
        }

        $this->ensureAddressColumns();
        $user = $this->currentUser();
        $items = $request->input('items', []);
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        if (empty($items)) {
            return response()->json([
                'status' => false,
                'message' => 'Your shopping bag is empty.',
            ], 422);
        }

        $contactName = trim((string)$request->input('contact_person_name', ''));
        $contactPhone = trim((string)$request->input('contact_person_number', ''));
        $streetAddress = trim((string)$request->input('address', ''));
        $addressType = trim((string)$request->input('address_type', 'Home')) ?: 'Home';
        $landmarkRoad = trim((string)$request->input('road', ''));
        $houseNo = trim((string)$request->input('house', ''));

        if ($request->input('order_type', 'delivery') !== 'self_pickup' && ($contactName === '' || $contactPhone === '' || $streetAddress === '')) {
            return response()->json([
                'status' => false,
                'message' => 'Please fill in your Delivery Address details (Contact Person Name, Phone Number, and Full Delivery Address).',
            ], 422);
        }

        // Authoritative server-side subtotal & stock validation (Flipkart/Meesho standard)
        $computedSubtotal = 0.0;
        $verifiedItems = [];
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? $item['quantity'] ?? 1));
            $prod = Product::find($item['id'] ?? 0);
            if ($prod) {
                if ($prod->total_stock > 0 && $qty > $prod->total_stock) {
                    return response()->json([
                        'status' => false,
                        'message' => "Only {$prod->total_stock} units of '{$prod->name}' are currently in stock.",
                    ], 422);
                }
                $unitPrice = (float)$prod->price;
            } else {
                $unitPrice = (float)($item['price'] ?? 50);
            }
            $computedSubtotal += ($unitPrice * $qty);
            $verifiedItems[] = [
                'product' => $prod,
                'item' => $item,
                'qty' => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        $subtotal = $computedSubtotal > 0 ? round($computedSubtotal, 2) : (float)$request->input('subtotal', 0);
        $discount = 0;
        $couponDiscount = max(0, (float)$request->input('coupon_discount', 0));
        $freeOver = (float)(Setting::getValue('free_delivery_over_amount', 69) ?: 69);
        $baseDelivery = (float)(Setting::getValue('delivery_charge', 40) ?: 40);
        $deliveryCharge = ($request->input('order_type', 'delivery') === 'self_pickup' || $subtotal >= $freeOver) ? 0.0 : $baseDelivery;
        $totalAmount = round(max(1, $subtotal - $couponDiscount + $deliveryCharge), 2);

        // Save or update the customer's delivery address in their account
        if ($contactName !== '' && $streetAddress !== '') {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
            $addressObj = Address::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address' => $streetAddress,
                ],
                [
                    'address_type' => $addressType,
                    'contact_person_name' => $contactName,
                    'contact_person_number' => $contactPhone,
                    'road' => $landmarkRoad,
                    'house' => $houseNo,
                    'latitude' => '19.0434',
                    'longitude' => '72.8633',
                    'is_default' => true,
                ]
            );
        } else {
            $addressId = $request->input('address_id');
            $addressObj = $addressId ? Address::find($addressId) : Address::where('user_id', $user->id)->orderByDesc('is_default')->first();
        }

        $fullFormattedAddress = $streetAddress;
        if ($houseNo !== '') {
            $fullFormattedAddress = $houseNo . ', ' . $fullFormattedAddress;
        }
        if ($landmarkRoad !== '') {
            $fullFormattedAddress .= ' (Landmark/Road: ' . $landmarkRoad . ')';
        }

        $deliveryAddress = [
            'address_type' => $addressType,
            'contact_person_name' => $contactName ?: ($addressObj->contact_person_name ?? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? ''))),
            'contact_person_number' => $contactPhone ?: ($addressObj->contact_person_number ?? ($user->phone ?? '')),
            'address' => $fullFormattedAddress ?: ($addressObj->address ?? ''),
            'road' => $landmarkRoad ?: ($addressObj->road ?? ''),
            'house' => $houseNo ?: ($addressObj->house ?? ''),
        ];

        // 1. Create order record in Database
        $order = Order::create([
            'user_id' => $user->id,
            'branch_id' => 1,
            'order_amount' => $totalAmount,
            'subtotal' => $subtotal,
            'product_discount' => $discount,
            'coupon_discount_amount' => $couponDiscount,
            'coupon_code' => $request->input('coupon_code'),
            'delivery_charge' => $deliveryCharge,
            'payment_method' => 'razorpay',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'order_type' => $request->input('order_type', 'delivery'),
            'time_slot' => $request->input('time_slot', 'Today, 09:00 AM - 11:00 PM'),
            'delivery_date' => now()->format('Y-m-d'),
            'delivery_address' => $deliveryAddress,
            'order_note' => $request->input('order_note'),
        ]);

        foreach ($verifiedItems as $vi) {
            $prod = $vi['product'];
            $item = $vi['item'];
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod ? $prod->id : ($item['id'] ?? 1),
                'product_name' => $prod ? $prod->name : ($item['name'] ?? 'Item'),
                'product_image' => $item['image'] ?? ($prod && !empty($prod->image) ? $prod->image[0] : ''),
                'unit' => $item['unit'] ?? (($prod->capacity ?? 1) . ' ' . ($prod->unit ?? 'pc')),
                'price' => $vi['unit_price'],
                'discount_on_product' => 0,
                'quantity' => $vi['qty'],
            ]);
        }

        // 2. Request Razorpay API to generate order
        $rzpResult = $razorpayService->createOrder($totalAmount, 'order_rcpt_' . $order->id, [
            'order_id' => (string) $order->id,
            'customer_phone' => (string) ($user->phone ?? '917679416294'),
            'customer_name' => (string) trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')),
        ]);

        if (!$rzpResult['success']) {
            return response()->json([
                'status' => false,
                'message' => $rzpResult['message'] ?? 'Failed to initialize Razorpay payment.',
            ], 500);
        }

        $order->update([
            'razorpay_order_id' => $rzpResult['order_id'],
        ]);

        $rawPhone = explode('/', (string)($user->phone ?? '917679416294'))[0];
        $cleanDigits = preg_replace('/\D+/', '', $rawPhone);
        $cleanPhone = strlen($cleanDigits) >= 10 ? ('+91' . substr($cleanDigits, -10)) : '+917679416294';

        return response()->json([
            'status' => true,
            'order_id' => $order->id,
            'razorpay_order_id' => $rzpResult['order_id'],
            'amount' => $rzpResult['amount'],
            'currency' => $rzpResult['currency'],
            'key' => $razorpayService->getKeyId(),
            'customer_name' => trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) ?: 'Valued Customer',
            'customer_email' => $user->email ?? 'customer@touchstains.com',
            'customer_phone' => $cleanPhone,
        ]);
    }

    /**
     * Verify Razorpay Payment Signature
     */
    public function verifyRazorpayPayment(Request $request, RazorpayService $razorpayService)
    {
        $orderId = $request->input('order_id');
        $razorpayOrderId = $request->input('razorpay_order_id');
        $razorpayPaymentId = $request->input('razorpay_payment_id');
        $razorpaySignature = $request->input('razorpay_signature');

        if (!$orderId || !$razorpayPaymentId) {
            return response()->json([
                'status' => false,
                'message' => 'Missing payment details from Razorpay.',
            ], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Verify cryptographic signature or Standard Checkout Payment ID
        $isValid = $razorpayService->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        if (!$isValid) {
            Log::warning("Razorpay signature mismatch for Order #{$orderId}");
            return response()->json([
                'status' => false,
                'message' => 'Payment signature verification failed. Please contact customer support.',
            ], 400);
        }

        // Fetch extra payment details
        $paymentDetails = $razorpayService->fetchPayment($razorpayPaymentId);

        $wasUnpaid = $order->payment_status !== 'paid';

        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'payment_method' => 'razorpay',
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'utr_number' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature,
            'razorpay_response' => $paymentDetails ? json_encode($paymentDetails) : null,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // Decrement stock & record inventory log on confirmed Razorpay payment
        if ($wasUnpaid) {
            foreach ($order->items as $oi) {
                $prod = Product::find($oi->product_id);
                if ($prod) {
                    $newStock = max(0, (int)$prod->total_stock - (int)$oi->quantity);
                    $prod->update(['total_stock' => $newStock]);
                    $prod->increment('popularity_count', (int)$oi->quantity);
                    try {
                        InventoryLog::create([
                            'product_id' => $prod->id,
                            'type' => 'stock_out',
                            'quantity' => (int)$oi->quantity,
                            'current_stock' => $newStock,
                            'notes' => "Order #{$order->id} paid via Razorpay ({$razorpayPaymentId})",
                            'user_id' => $order->user_id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('InventoryLog skip: ' . $e->getMessage());
                    }
                }
            }
        }

        // Award 5% loyalty points
        $user = $order->user ?: $this->currentUser();
        $earnedPoints = (int)floor($order->order_amount * 0.05);
        if ($user && $earnedPoints > 0) {
            $user->increment('loyalty_points', $earnedPoints);
        }

        AuditLog::record('Payment', 'Razorpay Online Payment', "Order #{$order->id} paid successfully via Razorpay Payment ID: {$razorpayPaymentId}");

        $this->triggerShiprocketAutoDispatch($order);

        return response()->json([
            'status' => true,
            'message' => 'Payment successful! Order confirmed.',
            'order_id' => $order->id,
            'redirect_url' => url('/order-success?id=' . $order->id),
        ]);
    }

    /**
     * Handle Razorpay Webhooks
     */
    public function handleRazorpayWebhook(Request $request)
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);

        if ($data && isset($data['event']) && in_array($data['event'], ['payment.captured', 'order.paid'])) {
            $payment = $data['payload']['payment']['entity'] ?? null;
            if ($payment) {
                $razorpayOrderId = $payment['order_id'] ?? null;
                $paymentId = $payment['id'] ?? null;
                if ($razorpayOrderId) {
                    $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
                    if ($order && $order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'paid',
                            'order_status' => 'confirmed',
                            'razorpay_payment_id' => $paymentId,
                            'razorpay_response' => json_encode($payment),
                        ]);
                        $this->triggerShiprocketAutoDispatch($order);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Place Order (Cash on Delivery or Standard Order)
     */
    public function placeOrder(Request $request)
    {
        if (Setting::getValue('payment_cod_active', '1') === '0') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cash on Delivery (COD) is currently disabled by store admin. Please choose Online Payment.',
                ], 422);
            }
            return back()->with('error', 'Cash on Delivery is currently disabled.');
        }

        $this->ensureAddressColumns();
        $user = $this->currentUser();
        $items = $request->input('items', []);
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        if (empty($items)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Shopping bag is empty.'], 422);
            }
            return back()->with('error', 'Shopping bag is empty.');
        }

        $contactName = trim((string)$request->input('contact_person_name', ''));
        $contactPhone = trim((string)$request->input('contact_person_number', ''));
        $streetAddress = trim((string)$request->input('address', ''));
        $addressType = trim((string)$request->input('address_type', 'Home')) ?: 'Home';
        $landmarkRoad = trim((string)$request->input('road', ''));
        $houseNo = trim((string)$request->input('house', ''));

        if ($request->input('order_type', 'delivery') !== 'self_pickup' && ($contactName === '' || $contactPhone === '' || $streetAddress === '')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please fill in your Delivery Address details (Contact Person Name, Phone Number, and Full Delivery Address).',
                ], 422);
            }
            return back()->with('error', 'Please fill in your Delivery Address details.');
        }

        // Authoritative server-side subtotal & stock validation
        $computedSubtotal = 0.0;
        $verifiedItems = [];
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? $item['quantity'] ?? 1));
            $prod = Product::find($item['id'] ?? 0);
            if ($prod) {
                if ($prod->total_stock > 0 && $qty > $prod->total_stock) {
                    $msg = "Only {$prod->total_stock} units of '{$prod->name}' are currently in stock.";
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => false, 'message' => $msg], 422);
                    }
                    return back()->with('error', $msg);
                }
                $unitPrice = (float)$prod->price;
            } else {
                $unitPrice = (float)($item['price'] ?? 50);
            }
            $computedSubtotal += ($unitPrice * $qty);
            $verifiedItems[] = [
                'product' => $prod,
                'item' => $item,
                'qty' => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        $subtotal = $computedSubtotal > 0 ? round($computedSubtotal, 2) : (float)$request->input('subtotal', 0);
        $discount = 0;
        $couponDiscount = max(0, (float)$request->input('coupon_discount', 0));
        $freeOver = (float)(Setting::getValue('free_delivery_over_amount', 69) ?: 69);
        $baseDelivery = (float)(Setting::getValue('delivery_charge', 40) ?: 40);
        $deliveryCharge = ($request->input('order_type', 'delivery') === 'self_pickup' || $subtotal >= $freeOver) ? 0.0 : $baseDelivery;
        $totalAmount = round(max(1, $subtotal - $couponDiscount + $deliveryCharge), 2);
        $paymentMethod = 'cash_on_delivery';

        if ($contactName !== '' && $streetAddress !== '') {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
            $addressObj = Address::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address' => $streetAddress,
                ],
                [
                    'address_type' => $addressType,
                    'contact_person_name' => $contactName,
                    'contact_person_number' => $contactPhone,
                    'road' => $landmarkRoad,
                    'house' => $houseNo,
                    'latitude' => '19.0434',
                    'longitude' => '72.8633',
                    'is_default' => true,
                ]
            );
        } else {
            $addressId = $request->input('address_id');
            $addressObj = $addressId ? Address::find($addressId) : Address::where('user_id', $user->id)->orderByDesc('is_default')->first();
        }

        $fullFormattedAddress = $streetAddress;
        if ($houseNo !== '') {
            $fullFormattedAddress = $houseNo . ', ' . $fullFormattedAddress;
        }
        if ($landmarkRoad !== '') {
            $fullFormattedAddress .= ' (Landmark/Road: ' . $landmarkRoad . ')';
        }

        $deliveryAddress = [
            'address_type' => $addressType,
            'contact_person_name' => $contactName ?: ($addressObj->contact_person_name ?? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? ''))),
            'contact_person_number' => $contactPhone ?: ($addressObj->contact_person_number ?? ($user->phone ?? '')),
            'address' => $fullFormattedAddress ?: ($addressObj->address ?? ''),
            'road' => $landmarkRoad ?: ($addressObj->road ?? ''),
            'house' => $houseNo ?: ($addressObj->house ?? ''),
        ];

        // Cash on Delivery Order placement
        $earnedPoints = (int)floor($totalAmount * 0.05);
        $order = Order::create([
            'user_id' => $user->id,
            'branch_id' => 1,
            'order_amount' => $totalAmount,
            'subtotal' => $subtotal,
            'product_discount' => $discount,
            'coupon_discount_amount' => $couponDiscount,
            'coupon_code' => $request->input('coupon_code'),
            'delivery_charge' => $deliveryCharge,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'order_status' => 'confirmed',
            'order_type' => $request->input('order_type', 'delivery'),
            'time_slot' => $request->input('time_slot', 'Today, 09:00 AM - 11:00 PM'),
            'delivery_date' => now()->format('Y-m-d'),
            'delivery_address' => $deliveryAddress,
            'contact_person_name' => $deliveryAddress['contact_person_name'] ?? $user->name,
            'contact_person_number' => $deliveryAddress['contact_person_number'] ?? $user->phone,
            'earned_loyalty_points' => $earnedPoints,
            'order_note' => $request->input('order_note'),
        ]);

        foreach ($verifiedItems as $vi) {
            $prod = $vi['product'];
            $item = $vi['item'];
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod ? $prod->id : ($item['id'] ?? 1),
                'product_name' => $prod ? $prod->name : ($item['name'] ?? 'Item'),
                'product_image' => $item['image'] ?? ($prod && !empty($prod->image) ? $prod->image[0] : ''),
                'unit' => $item['unit'] ?? (($prod->capacity ?? 1) . ' ' . ($prod->unit ?? 'pc')),
                'price' => $vi['unit_price'],
                'discount_on_product' => 0,
                'quantity' => $vi['qty'],
            ]);

            // Decrement stock & log inventory movement
            if ($prod) {
                $newStock = max(0, (int)$prod->total_stock - (int)$vi['qty']);
                $prod->update(['total_stock' => $newStock]);
                $prod->increment('popularity_count', (int)$vi['qty']);
                try {
                    InventoryLog::create([
                        'product_id' => $prod->id,
                        'type' => 'stock_out',
                        'quantity' => (int)$vi['qty'],
                        'current_stock' => $newStock,
                        'notes' => "COD Order #{$order->id} confirmed",
                        'user_id' => $user->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('InventoryLog skip: ' . $e->getMessage());
                }
            }
        }

        // Award 5% loyalty points safely
        if ($earnedPoints > 0) {
            try {
                $user->increment('loyalty_points', $earnedPoints);
            } catch (\Throwable $e) {
                Log::warning('Loyalty points increment skip: ' . $e->getMessage());
            }
        }

        try {
            AuditLog::record('Order', 'COD Order Placed', "Order #{$order->id} confirmed with Cash on Delivery");
        } catch (\Throwable $e) {
            Log::warning('AuditLog skip: ' . $e->getMessage());
        }

        $this->triggerShiprocketAutoDispatch($order);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully with Cash on Delivery!',
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'razorpay_payment_id' => null,
                'earned_points' => $earnedPoints,
                'redirect_url' => url('/order-success?id=' . $order->id),
            ]);
        }

        return redirect('/order-success?id=' . $order->id);
    }

    /**
     * Place Order via Direct UPI Payment
     */
    public function placeUpiOrder(Request $request)
    {
        if (Setting::getValue('payment_upi_active', '1') === '0') {
            return response()->json([
                'status' => false,
                'message' => 'UPI Payment is currently disabled by store admin. Please choose another payment method.',
            ], 422);
        }

        $this->ensureAddressColumns();
        $user = $this->currentUser();
        $items = $request->input('items', []);
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        if (empty($items)) {
            return response()->json(['status' => false, 'message' => 'Your shopping bag is empty.'], 422);
        }

        $contactName = trim((string)$request->input('contact_person_name', ''));
        $contactPhone = trim((string)$request->input('contact_person_number', ''));
        $streetAddress = trim((string)$request->input('address', ''));
        $addressType = trim((string)$request->input('address_type', 'Home')) ?: 'Home';
        $landmarkRoad = trim((string)$request->input('road', ''));
        $houseNo = trim((string)$request->input('house', ''));

        if ($request->input('order_type', 'delivery') !== 'self_pickup' && ($contactName === '' || $contactPhone === '' || $streetAddress === '')) {
            return response()->json([
                'status' => false,
                'message' => 'Please fill in your Delivery Address details (Contact Person Name, Phone Number, and Full Delivery Address).',
            ], 422);
        }

        $computedSubtotal = 0.0;
        $verifiedItems = [];
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? $item['quantity'] ?? 1));
            $prod = Product::find($item['id'] ?? 0);
            if ($prod) {
                if ($prod->total_stock > 0 && $qty > $prod->total_stock) {
                    return response()->json([
                        'status' => false,
                        'message' => "Only {$prod->total_stock} units of '{$prod->name}' are currently in stock.",
                    ], 422);
                }
                $unitPrice = (float)$prod->price;
            } else {
                $unitPrice = (float)($item['price'] ?? 50);
            }
            $computedSubtotal += ($unitPrice * $qty);
            $verifiedItems[] = [
                'product' => $prod,
                'item' => $item,
                'qty' => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        $subtotal = $computedSubtotal > 0 ? round($computedSubtotal, 2) : (float)$request->input('subtotal', 0);
        $discount = 0;
        $couponDiscount = max(0, (float)$request->input('coupon_discount', 0));
        $freeOver = (float)(Setting::getValue('free_delivery_over_amount', 69) ?: 69);
        $baseDelivery = (float)(Setting::getValue('delivery_charge', 40) ?: 40);
        $deliveryCharge = ($request->input('order_type', 'delivery') === 'self_pickup' || $subtotal >= $freeOver) ? 0.0 : $baseDelivery;
        $totalAmount = round(max(1, $subtotal - $couponDiscount + $deliveryCharge), 2);

        if ($contactName !== '' && $streetAddress !== '') {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
            $addressObj = Address::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address' => $streetAddress,
                ],
                [
                    'address_type' => $addressType,
                    'contact_person_name' => $contactName,
                    'contact_person_number' => $contactPhone,
                    'road' => $landmarkRoad,
                    'house' => $houseNo,
                    'latitude' => '19.0434',
                    'longitude' => '72.8633',
                    'is_default' => true,
                ]
            );
        } else {
            $addressId = $request->input('address_id');
            $addressObj = $addressId ? Address::find($addressId) : Address::where('user_id', $user->id)->orderByDesc('is_default')->first();
        }

        $fullFormattedAddress = $streetAddress;
        if ($houseNo !== '') {
            $fullFormattedAddress = $houseNo . ', ' . $fullFormattedAddress;
        }
        if ($landmarkRoad !== '') {
            $fullFormattedAddress .= ' (Landmark/Road: ' . $landmarkRoad . ')';
        }

        $deliveryAddress = [
            'address_type' => $addressType,
            'contact_person_name' => $contactName ?: ($addressObj->contact_person_name ?? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? ''))),
            'contact_person_number' => $contactPhone ?: ($addressObj->contact_person_number ?? ($user->phone ?? '')),
            'address' => $fullFormattedAddress ?: ($addressObj->address ?? ''),
            'road' => $landmarkRoad ?: ($addressObj->road ?? ''),
            'house' => $houseNo ?: ($addressObj->house ?? ''),
        ];

        $earnedPoints = (int)floor($totalAmount * 0.05);
        $order = Order::create([
            'user_id' => $user->id,
            'branch_id' => 1,
            'order_amount' => $totalAmount,
            'subtotal' => $subtotal,
            'product_discount' => $discount,
            'coupon_discount_amount' => $couponDiscount,
            'coupon_code' => $request->input('coupon_code'),
            'delivery_charge' => $deliveryCharge,
            'payment_method' => 'upi',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'order_type' => $request->input('order_type', 'delivery'),
            'time_slot' => $request->input('time_slot', 'Today, 09:00 AM - 11:00 PM'),
            'delivery_date' => now()->format('Y-m-d'),
            'delivery_address' => $deliveryAddress,
            'contact_person_name' => $deliveryAddress['contact_person_name'] ?? $user->name,
            'contact_person_number' => $deliveryAddress['contact_person_number'] ?? $user->phone,
            'earned_loyalty_points' => $earnedPoints,
            'order_note' => $request->input('order_note'),
        ]);

        foreach ($verifiedItems as $vi) {
            $prod = $vi['product'];
            $item = $vi['item'];
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $prod ? $prod->id : ($item['id'] ?? 1),
                'product_name' => $prod ? $prod->name : ($item['name'] ?? 'Item'),
                'product_image' => $item['image'] ?? ($prod && !empty($prod->image) ? $prod->image[0] : ''),
                'unit' => $item['unit'] ?? (($prod->capacity ?? 1) . ' ' . ($prod->unit ?? 'pc')),
                'price' => $vi['unit_price'],
                'discount_on_product' => 0,
                'quantity' => $vi['qty'],
            ]);

            if ($prod) {
                $newStock = max(0, (int)$prod->total_stock - (int)$vi['qty']);
                $prod->update(['total_stock' => $newStock]);
                $prod->increment('popularity_count', (int)$vi['qty']);
                try {
                    InventoryLog::create([
                        'product_id' => $prod->id,
                        'type' => 'stock_out',
                        'quantity' => (int)$vi['qty'],
                        'current_stock' => $newStock,
                        'notes' => "UPI Order #{$order->id} initiated",
                        'user_id' => $user->id,
                    ]);
                } catch (\Throwable $e) {}
            }
        }

        // Record pending payment in BankTransfer table for admin reconciliation
        try {
            BankTransfer::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'bank_account_id' => 1,
                'amount' => $totalAmount,
                'payment_method' => 'upi',
                'status' => 'verification_pending',
            ]);
        } catch (\Throwable $e) {}

        $upiId = Setting::getValue('payment_upi_id', '7039214465-3@ybl');
        $upiName = Setting::getValue('payment_upi_name', 'Naushad Ali Meher Ali');
        $amtFormatted = number_format($totalAmount, 2, '.', '');
        $txnNote = "Order #{$order->id} TOUCHSTAINS";
        $upiIntentUrl = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . $amtFormatted . "&cu=INR&tn=" . urlencode($txnNote) . "&mc=0000&mode=02&purpose=00";

        try {
            AuditLog::record('Order', 'UPI Order Placed', "Order #{$order->id} placed via UPI ({$amtFormatted})");
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => true,
            'message' => 'UPI Order initiated! Redirecting to payment screen...',
            'order_id' => $order->id,
            'amount' => $totalAmount,
            'upi_id' => $upiId,
            'upi_name' => $upiName,
            'upi_intent_url' => $upiIntentUrl,
            'redirect_url' => route('order.upi-payment', ['id' => $order->id]),
        ]);
    }

    /**
     * Dedicated UPI Payment Gateway & QR Screen
     */
    public function upiPaymentPage($id)
    {
        $shared = $this->getSharedData();
        $order = Order::with(['items', 'user'])->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return redirect('/order-success?id=' . $order->id)->with('success', 'Order payment already verified!');
        }

        $upiId = Setting::getValue('payment_upi_id', '7039214465-3@ybl');
        $upiName = Setting::getValue('payment_upi_name', 'Naushad Ali Meher Ali');
        $upiQrImage = Setting::getValue('payment_upi_qr_image', 'payment/upi_phonepe_qr.jpg');
        $amtFormatted = number_format($order->order_amount, 2, '.', '');
        $txnNote = "Order #{$order->id} TOUCHSTAINS";
        $upiIntentUrl = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . $amtFormatted . "&cu=INR&tn=" . urlencode($txnNote) . "&mc=0000&mode=02&purpose=00";

        return view('pages.upi-payment', array_merge($shared, [
            'order' => $order,
            'upiId' => $upiId,
            'upiName' => $upiName,
            'upiQrImage' => $upiQrImage,
            'upiIntentUrl' => $upiIntentUrl,
            'pageTitle' => 'Complete UPI Payment — Order #' . $order->id,
        ]));
    }

    /**
     * Confirm UPI Payment by Customer (Submit UTR / Ref Number)
     */
    public function confirmUpiPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $utr = trim((string)$request->input('utr_number', ''));

        if ($utr === '') {
            $utr = 'UPI-' . strtoupper(substr(md5(time() . $order->id), 0, 10));
        }

        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'utr_number' => $utr,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // Update BankTransfer record
        $transfer = BankTransfer::where('order_id', $order->id)->first();
        if ($transfer) {
            $transfer->update([
                'utr_number' => $utr,
                'status' => 'payment_confirmed',
                'approved_at' => now(),
            ]);
        }

        try {
            AuditLog::record('Payments', 'UPI Payment Confirmed', "Order #{$order->id} payment confirmed via UPI UTR {$utr}");
        } catch (\Throwable $e) {}

        // Trigger auto-shipment if active
        $this->triggerShiprocketAutoDispatch($order);

        return redirect('/order-success?id=' . $order->id)->with('success', '✓ Payment confirmed successfully! Order placed.');
    }

    public function submitBankTransfer(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|string',
            'utr_number' => 'required|string|min:6|max:64',
            'payment_screenshot' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
        ]);

        $user = $this->currentUser();
        $order = Order::findOrFail($validated['order_id']);

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'receipt_order_' . $order->id . '_' . time() . '_' . Str::random(10) . '.' . $ext;
                $file->storeAs('payment_receipts', $filename, 'public');
                $destDir = public_path('storage/payment_receipts');
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($file->getRealPath(), $destDir . DIRECTORY_SEPARATOR . $filename);
                $screenshotPath = 'payment_receipts/' . $filename;
            }
        }

        $bankTransfer = null;
        DB::transaction(function () use ($order, $user, $validated, $screenshotPath, &$bankTransfer) {
            $bankTransfer = BankTransfer::firstOrNew(['order_id' => $order->id]);
            $prevStatus = $bankTransfer->status ?? 'awaiting_payment';

            $bankTransfer->user_id = $user->id;
            $bankTransfer->amount = (float)$validated['amount'];
            $bankTransfer->transaction_date = $validated['transaction_date'];
            $bankTransfer->utr_number = trim($validated['utr_number']);
            if ($screenshotPath) {
                $bankTransfer->screenshot_path = $screenshotPath;
            }
            $bankTransfer->status = 'verification_pending';
            $bankTransfer->rejection_reason = null; // Clear previous rejection
            $bankTransfer->save();

            // Automatically update order payment status to Verification Pending
            $order->update([
                'payment_status' => 'verification_pending',
            ]);

            // Create audit log entry
            BankTransferLog::create([
                'bank_transfer_id' => $bankTransfer->id,
                'order_id' => $order->id,
                'action' => 'submitted',
                'previous_status' => $prevStatus,
                'new_status' => 'verification_pending',
                'performed_by' => $user->id,
                'actor_type' => 'customer',
                'notes' => 'Customer submitted UTR: ' . $validated['utr_number'] . ' for verification.',
            ]);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment details submitted successfully! Your payment is now under verification.',
                'order_id' => $order->id,
                'payment_status' => 'verification_pending',
                'bank_transfer' => $bankTransfer,
            ]);
        }

        return back()->with('success', 'Payment details submitted successfully! Verification is in progress.');
    }

    public function orderSuccess(Request $request, $id = null)
    {
        $shared = $this->getSharedData();
        $orderId = $id ?: $request->query('id');
        $order = $orderId ? Order::with(['items', 'bankTransfer.logs', 'user'])->find($orderId) : Order::with(['items', 'bankTransfer.logs', 'user'])->latest()->first();
        $bankAccount = BankAccount::getActiveAccount();

        return view('pages.order-success', array_merge($shared, [
            'pageId' => 'order-success',
            'pageTitle' => 'Order Placed Successfully — TOUCHSTAINS',
            'order' => $order,
            'bankAccount' => $bankAccount,
            'bankTransfer' => $order ? $order->bankTransfer : null,
        ]));
    }

    public function orders()
    {
        $shared = $this->getSharedData();
        $orders = Auth::check()
            ? Order::where('user_id', Auth::id())
                ->with(['items', 'bankTransfer', 'shipment'])
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('pages.orders', array_merge($shared, [
            'pageId' => 'orders',
            'pageTitle' => 'My Orders — TOUCHSTAINS',
            'orders' => $orders,
        ]));
    }

    public function orderDetails(Request $request, $id = null)
    {
        $shared = $this->getSharedData();
        $orderId = $id ?: $request->query('id');
        $order = $orderId 
            ? Order::with(['items', 'bankTransfer.logs', 'user', 'shipment.trackingEvents'])->find($orderId) 
            : Order::with(['items', 'bankTransfer.logs', 'user', 'shipment.trackingEvents'])->latest()->first();
        $bankAccount = BankAccount::getActiveAccount();

        return view('pages.order-details', array_merge($shared, [
            'pageId' => 'order-details',
            'pageTitle' => 'Order Details #' . ($order->id ?? '100248') . ' — TOUCHSTAINS',
            'order' => $order,
            'bankAccount' => $bankAccount,
            'bankTransfer' => $order ? $order->bankTransfer : null,
        ]));
    }

    public function cancelOrder($id, Request $request)
    {
        $order = Order::with('items')->find($id);
        if ($order && in_array($order->order_status, ['pending', 'confirmed'])) {
            $order->update(['order_status' => 'canceled']);
            // Restore product stock on cancellation
            foreach ($order->items as $oi) {
                $prod = Product::find($oi->product_id);
                if ($prod) {
                    $newStock = (int)$prod->total_stock + (int)$oi->quantity;
                    $prod->update(['total_stock' => $newStock]);
                    try {
                        InventoryLog::create([
                            'product_id' => $prod->id,
                            'type' => 'stock_in',
                            'quantity' => (int)$oi->quantity,
                            'current_stock' => $newStock,
                            'notes' => "Stock restored from canceled Order #{$order->id}",
                            'user_id' => $order->user_id,
                        ]);
                    } catch (\Throwable $e) {}
                }
            }
        }
        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'order' => $order]);
        }
        return back()->with('success', 'Order #' . $id . ' has been canceled and stock restored.');
    }

    public function trackOrder(Request $request)
    {
        $shared = $this->getSharedData();
        $orderId = $request->query('id');
        $order = $orderId 
            ? Order::with(['items', 'shipment.trackingEvents'])->find($orderId) 
            : Order::with(['items', 'shipment.trackingEvents'])->latest()->first();

        return view('pages.track-order', array_merge($shared, [
            'pageId' => 'track-order',
            'pageTitle' => 'Track Order — TOUCHSTAINS',
            'order' => $order,
        ]));
    }

    /**
     * Public Order & Product Verification Page (Flipkart/Amazon style)
     * Scanned directly from parcel shipping label or tax invoice QR code
     */
    public function verifyOrder(Request $request, $id = null)
    {
        $identifier = $id ?: ($request->query('id') ?: $request->query('awb'));
        $order = null;

        if ($identifier) {
            // Check by Order ID first
            $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($identifier);

            // If not found by ID, check by AWB number
            if (!$order) {
                $shipment = Shipment::where('awb_number', $identifier)
                    ->orWhere('shiprocket_order_id', $identifier)
                    ->first();
                if ($shipment) {
                    $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($shipment->order_id);
                }
            }
        }

        // If still not found, try getting latest order
        if (!$order) {
            $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->latest()->first();
        }

        // If database has 0 orders, provide active product demo so Google Lens scan always shows product details
        if (!$order) {
            $product = Product::first() ?: new Product([
                'id' => 1,
                'name' => 'TOUCHSTAINS™ Instant Fabric Stain & Rust Remover (250ml)',
                'sku' => 'TS-SR-250ML',
                'barcode' => '8901020304050',
                'price' => 299.00,
                'unit' => '250ml Bottle',
                'image' => ['touchstains-logo.png'],
            ]);

            $sampleItem = new OrderItem([
                'id' => 1,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => 'touchstains-logo.png',
                'unit' => $product->unit ?: '250ml Bottle',
                'price' => $product->price ?: 299.00,
                'quantity' => 1,
            ]);
            $sampleItem->setRelation('product', $product);

            $order = new Order([
                'id' => $identifier ?: 1,
                'order_amount' => 299.00,
                'payment_status' => 'unpaid',
                'order_status' => 'confirmed',
                'payment_method' => 'cash_on_delivery',
                'delivery_address' => json_encode([
                    'contact_person_name' => 'Verified Customer',
                    'contact_person_number' => '+91 7679416294',
                    'address' => '24 Bharat Mata Society, Chembur East',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'zip' => '400074'
                ]),
                'created_at' => now(),
            ]);
            $order->setRelation('items', collect([$sampleItem]));
        }

        $shared = $this->getSharedData();

        return view('pages.verify-order', array_merge($shared, [
            'pageId' => 'verify-order',
            'pageTitle' => "Verified Order #{$order->id} — TOUCHSTAINS",
            'order' => $order,
            'shipment' => $order->shipment ?? null,
        ]));
    }

    /**
     * Smart Product & Order Tracking QR Endpoint.
     * Scanned by Google Lens, Phone Camera, or QR Scanners from Tax Invoice or Shipping Label.
     * Displays Order Status, Live Product Photo, SKU, Qty, Price, Customer Info, and Shiprocket Tracking.
     */
    public function scanOrder(Request $request, $token = null)
    {
        $identifier = $token ?: ($request->query('token') ?: $request->query('id') ?: $request->query('order'));
        $order = null;

        if ($identifier) {
            // 1. Direct Numeric ID check
            if (is_numeric($identifier)) {
                $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($identifier);
            }

            // 2. Token formatted check: ORD-{id}-{hash}
            if (!$order && preg_match('/^ORD-(\d+)/i', $identifier, $matches)) {
                $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($matches[1]);
            }

            // 3. AWB number check
            if (!$order) {
                $shipment = Shipment::where('awb_number', $identifier)
                    ->orWhere('shiprocket_order_id', $identifier)
                    ->first();
                if ($shipment) {
                    $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($shipment->order_id);
                }
            }
        }

        // 4. Fallback to latest order
        if (!$order) {
            $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->latest()->first();
        }

        // 5. Clean production demo fallback if database has 0 orders
        if (!$order) {
            $product = Product::where('status', 1)->first() ?: Product::first();
            if (!$product) {
                $product = new Product([
                    'id' => 1025,
                    'name' => 'TOUCHSTAINS™ Instant Fabric Stain & Rust Remover (250ml)',
                    'sku' => 'TS-SR-250ML',
                    'barcode' => '8901020304050',
                    'price' => 299.00,
                    'market_price' => 399.00,
                    'unit' => '250ml Bottle',
                    'image' => ['touchstains-logo.png'],
                    'total_stock' => 500,
                    'status' => 1,
                ]);
            }

            $sampleItem = new OrderItem([
                'id' => 1,
                'order_id' => 1025,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => !empty($product->images_list) ? $product->images_list[0] : 'touchstains-logo.png',
                'unit' => $product->unit ?: '250ml Bottle',
                'price' => $product->price ?: 299.00,
                'quantity' => 2,
            ]);
            $sampleItem->setRelation('product', $product);

            $order = new Order([
                'id' => 1025,
                'order_amount' => ($product->price ?: 299.00) * 2,
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'payment_method' => 'online',
                'delivery_address' => json_encode([
                    'contact_person_name' => 'Rahul Das',
                    'contact_person_number' => '+91 7679416294',
                    'address' => 'Shop No. 3 & 4, Tower B, Braumonte, Sion Koliwada',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'zip' => '400074'
                ]),
                'created_at' => now(),
            ]);
            $order->setRelation('items', collect([$sampleItem]));
        }

        $shipment = $order ? $order->shipment : null;
        $shared = $this->getSharedData();

        return view('pages.smart-order-scan', array_merge($shared, [
            'pageId' => 'smart-order-scan',
            'pageTitle' => "Order #{$order->id} — Live Package & Product Tracking — TOUCHSTAINS",
            'order' => $order,
            'shipment' => $shipment,
        ]));
    }

    /**
     * Dedicated Product Scan View for Google Lens, Mobile Scanner & Delivery Riders.
     * Scanned directly from product barcode on Tax Invoice or Product Packaging.
     * Displays product photo, title, ID, SKU, price, stock, and associated order details.
     */
    public function scanProduct(Request $request, $id = null)
    {
        $identifier = $id ?: ($request->query('id') ?: $request->query('sku') ?: $request->query('barcode'));
        $product = null;
        $order = null;

        if ($identifier) {
            // 1. Try finding product by ID
            if (is_numeric($identifier)) {
                $product = Product::find($identifier);
            }

            // 2. Try finding by SKU
            if (!$product) {
                $product = Product::where('sku', $identifier)->first();
            }

            // 3. Try finding by Barcode
            if (!$product) {
                $product = Product::where('barcode', $identifier)->first();
            }

            // 4. Try checking if identifier is an Order ID
            if (!$product && is_numeric($identifier)) {
                $matchedOrder = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($identifier);
                if ($matchedOrder && $matchedOrder->items->isNotEmpty()) {
                    $order = $matchedOrder;
                    $firstItem = $order->items->first();
                    $product = $firstItem->product ?: Product::find($firstItem->product_id);
                }
            }

            // 5. Try checking if identifier is an AWB number
            if (!$product) {
                $matchedShipment = Shipment::where('awb_number', $identifier)
                    ->orWhere('shiprocket_order_id', $identifier)
                    ->first();
                if ($matchedShipment) {
                    $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($matchedShipment->order_id);
                    if ($order && $order->items->isNotEmpty()) {
                        $firstItem = $order->items->first();
                        $product = $firstItem->product ?: Product::find($firstItem->product_id);
                    }
                }
            }
        }

        // Check if an order query param is provided (e.g. /p/1?order=12)
        $orderId = $request->query('order');
        if ($orderId) {
            $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($orderId);
        }

        // If order found but product not found, take first product from the order
        if ($order && !$product && $order->items->isNotEmpty()) {
            $firstItem = $order->items->first();
            $product = $firstItem->product ?: Product::find($firstItem->product_id);
        }

        // If no product found yet, get first active product or create sample fallback
        if (!$product) {
            $product = Product::where('status', 1)->first() ?: Product::first();
        }

        if (!$product) {
            $product = new Product([
                'id' => 1,
                'name' => 'TOUCHSTAINS™ Instant Fabric Stain & Rust Remover (250ml)',
                'sku' => 'TS-SR-250ML',
                'barcode' => '8901020304050',
                'price' => 299.00,
                'market_price' => 399.00,
                'unit' => '250ml Bottle',
                'description' => 'Professional-grade instant stain and rust remover. Formulated for clothes, garments, uniforms, and household fabrics. Enzyme-powered, color-safe, and chlorine-free.',
                'image' => ['touchstains-logo.png'],
                'total_stock' => 500,
                'status' => 1,
            ]);
        }

        // If order is still null, look for the most recent order containing this product
        if (!$order && $product->id) {
            $recentOrderItem = OrderItem::where('product_id', $product->id)->latest()->first();
            if ($recentOrderItem) {
                $order = Order::with(['items.product', 'shipment.trackingEvents', 'user'])->find($recentOrderItem->order_id);
            }
        }

        // If still no order, construct a clean verified demo order context for complete verification view
        if (!$order) {
            $sampleItem = new OrderItem([
                'id' => 1,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => !empty($product->images_list) ? $product->images_list[0] : 'touchstains-logo.png',
                'unit' => $product->unit ?: '250ml Bottle',
                'price' => $product->price ?: 299.00,
                'quantity' => 1,
            ]);
            $sampleItem->setRelation('product', $product);

            $order = new Order([
                'id' => 1,
                'order_amount' => $product->price ?: 299.00,
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'payment_method' => 'online',
                'delivery_address' => json_encode([
                    'contact_person_name' => 'Verified Customer',
                    'contact_person_number' => '+91 7679416294',
                    'address' => '24 Bharat Mata Society, Chembur East',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'zip' => '400074'
                ]),
                'created_at' => now(),
            ]);
            $order->setRelation('items', collect([$sampleItem]));
        }

        $shipment = $order ? $order->shipment : null;
        $shared = $this->getSharedData();

        return view('pages.product-scan', array_merge($shared, [
            'pageId' => 'product-scan',
            'pageTitle' => "{$product->name} — Verified Product Scan — TOUCHSTAINS",
            'product' => $product,
            'order' => $order,
            'shipment' => $shipment,
        ]));
    }

    public function wishlist()
    {
        $shared = $this->getSharedData();
        $wishlistItems = Auth::check()
            ? Wishlist::where('user_id', Auth::id())
                ->with('product')
                ->get()
                ->pluck('product')
                ->filter()
            : collect();

        return view('pages.wishlist', array_merge($shared, [
            'pageId' => 'wishlist',
            'pageTitle' => 'My Favourite List — TOUCHSTAINS',
            'wishlistItems' => $wishlistItems,
        ]));
    }

    public function toggleWishlist(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'require_auth' => true,
                'message' => 'Please register or login to manage your wishlist.',
            ], 401);
        }
        $user = Auth::user();
        $productId = (int)$request->input('product_id');
        $existing = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->first();
        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $productId]);
            $action = 'added';
        }
        return response()->json(['status' => true, 'action' => $action]);
    }

    /**
     * Validate & apply coupon from SQLite coupons table (Flipkart/Meesho style)
     */
    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim((string)$request->input('code', '')));
        $subtotal = (float)$request->input('subtotal', 0);

        if ($code === '') {
            return response()->json(['status' => false, 'message' => 'Please enter a coupon code.'], 422);
        }

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->where('status', 1)->first();
        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired coupon code.'], 404);
        }

        $minPurchase = (float)($coupon->min_purchase ?? 0);
        if ($subtotal < $minPurchase) {
            return response()->json([
                'status' => false,
                'message' => "Minimum order amount of ₹{$minPurchase} required for code {$coupon->code}.",
            ], 422);
        }

        $discVal = (float)($coupon->discount ?? 0);
        $maxDisc = (float)($coupon->max_discount ?? 0);
        if ($coupon->discount_type === 'percent') {
            $discountAmount = round(($subtotal * $discVal) / 100, 2);
            if ($maxDisc > 0) {
                $discountAmount = min($discountAmount, $maxDisc);
            }
        } else {
            $discountAmount = min($subtotal, $discVal);
        }

        return response()->json([
            'status' => true,
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $discVal,
            'max_discount' => $maxDisc,
            'min_purchase' => $minPurchase,
            'discount_amount' => $discountAmount,
            'message' => "Coupon {$coupon->code} applied! You saved ₹{$discountAmount}.",
        ]);
    }

    /**
     * Submit customer product rating & review
     */
    public function storeReview(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'require_auth' => true, 'message' => 'Please login or register to write a review.'], 401);
            }
            return redirect('/login')->with('error', 'Please login or register to write a review.');
        }

        $product = Product::findOrFail($id);
        $rating = max(1, min(5, (int)$request->input('rating', 5)));
        $comment = trim((string)$request->input('comment', ''));

        Review::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $rating,
            'comment' => $comment ?: 'Great product!',
        ]);

        // Recompute average rating on Product
        $avg = round((float)Review::where('product_id', $product->id)->avg('rating'), 1);
        $cnt = Review::where('product_id', $product->id)->count();
        $product->update([
            'rating' => [['average' => (string)$avg, 'product_id' => $product->id, 'count' => $cnt]],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Thank you! Your review has been published.']);
        }
        return back()->with('success', 'Thank you! Your review has been published.');
    }

    public function wallet()
    {
        $shared = $this->getSharedData();
        return view('pages.wallet', array_merge($shared, [
            'pageId' => 'wallet',
            'pageTitle' => 'Wallet & Loyalty Points — TOUCHSTAINS',
        ]));
    }

    public function convertPoints(Request $request)
    {
        $user = $this->currentUser();
        $points = (int)$request->input('points', $user->loyalty_points);
        $rate = (float)Setting::getValue('loyalty_point_exchange_rate', 1);

        if ($points > 0 && $user->loyalty_points >= $points) {
            $credit = round($points / max(1, $rate), 2);
            $user->decrement('loyalty_points', $points);
            $user->increment('wallet_balance', $credit);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'wallet_balance' => $user->fresh()->wallet_balance,
                    'loyalty_points' => $user->fresh()->loyalty_points,
                    'message' => "Converted {$points} points into ₹{$credit} wallet balance!",
                ]);
            }
            return back()->with('success', "Converted {$points} points into ₹{$credit} wallet balance!");
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => false, 'message' => 'Insufficient loyalty points.'], 422);
        }
        return back()->with('error', 'Insufficient loyalty points.');
    }

    public function addFund(Request $request)
    {
        $user = $this->currentUser();
        $amount = (float)$request->input('amount', 100);
        if ($amount > 0) {
            $user->increment('wallet_balance', $amount);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'wallet_balance' => $user->fresh()->wallet_balance,
            ]);
        }
        return back()->with('success', "₹{$amount} added to your wallet!");
    }

    public function coupons()
    {
        $shared = $this->getSharedData();
        return view('pages.coupons', array_merge($shared, [
            'pageId' => 'coupons',
            'pageTitle' => 'Available Coupons — TOUCHSTAINS',
        ]));
    }

    public function addresses()
    {
        $this->ensureAddressColumns();
        $shared = $this->getSharedData();
        $isCustomerRegistered = Auth::check();
        $addresses = $isCustomerRegistered
            ? Address::where('user_id', Auth::id())->orderByDesc('is_default')->get()
            : collect();
        return view('pages.address', array_merge($shared, [
            'pageId' => 'address',
            'pageTitle' => 'Saved Addresses — TOUCHSTAINS',
            'addresses' => $addresses,
            'isCustomerRegistered' => $isCustomerRegistered,
        ]));
    }

    public function storeAddress(Request $request)
    {
        $this->ensureAddressColumns();
        $user = $this->currentUser();
        $contactName = trim((string)$request->input('contact_person_name', '')) ?: trim($user->f_name . ' ' . $user->l_name);
        $contactPhone = trim((string)$request->input('contact_person_number', '')) ?: $user->phone;
        $streetAddress = trim((string)$request->input('address', ''));

        if ($streetAddress === '') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Please enter your delivery address.'], 422);
            }
            return back()->with('error', 'Please enter your delivery address.');
        }

        Address::where('user_id', $user->id)->update(['is_default' => false]);

        $addr = Address::create([
            'user_id' => $user->id,
            'address_type' => $request->input('address_type', 'Home'),
            'contact_person_name' => $contactName,
            'contact_person_number' => $contactPhone,
            'address' => $streetAddress,
            'road' => $request->input('road'),
            'house' => $request->input('house'),
            'floor' => $request->input('floor'),
            'latitude' => '19.0434',
            'longitude' => '72.8633',
            'is_default' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Delivery address saved to your account!',
                'address' => $addr,
            ]);
        }
        return back()->with('success', 'Delivery address saved to your account successfully!');
    }

    public function deleteAddress($id, Request $request)
    {
        Address::where('id', $id)->delete();
        if ($request->wantsJson()) {
            return response()->json(['status' => true]);
        }
        return back()->with('success', 'Address removed.');
    }

    public function account()
    {
        $shared = $this->getSharedData();
        $ordersCount = Order::where('user_id', $shared['currentUser']->id)->count();
        return view('pages.account', array_merge($shared, [
            'pageId' => 'account',
            'pageTitle' => 'My Profile — TOUCHSTAINS',
            'ordersCount' => $ordersCount,
        ]));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->currentUser();
        $user->update([
            'name' => trim($request->input('f_name', $user->f_name) . ' ' . $request->input('l_name', $user->l_name)),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'user' => $user->fresh()]);
        }
        return back()->with('success', 'Profile updated successfully!');
    }

    public function refer()
    {
        $shared = $this->getSharedData();
        return view('pages.refer', array_merge($shared, [
            'pageId' => 'refer',
            'pageTitle' => 'Refer & Earn — TOUCHSTAINS',
        ]));
    }

    public function notifications()
    {
        $shared = $this->getSharedData();
        return view('pages.notifications', array_merge($shared, [
            'pageId' => 'notifications',
            'pageTitle' => 'Notifications — TOUCHSTAINS',
        ]));
    }

    public function chat()
    {
        $shared = $this->getSharedData();
        $messages = Message::where('user_id', $shared['currentUser']->id)->orderBy('id')->get();
        return view('pages.chat', array_merge($shared, [
            'pageId' => 'chat',
            'pageTitle' => 'Live Support Chat — TOUCHSTAINS',
            'messages' => $messages,
        ]));
    }

    public function sendMessage(Request $request)
    {
        $user = $this->currentUser();
        $msgText = trim($request->input('message', ''));
        if ($msgText !== '') {
            $msg = Message::create([
                'user_id' => $user->id,
                'sender' => 'customer',
                'message' => $msgText,
            ]);
            $reply = Message::create([
                'user_id' => $user->id,
                'sender' => 'admin',
                'message' => 'Thank you for messaging TOUCHSTAINS Support (Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022)! Our team is reviewing your query and will assist you immediately. For urgent assistance, call +91 7679416294 / 7039214465 or email touchstains@gmail.com.',
            ]);
            if ($request->wantsJson()) {
                return response()->json(['status' => true, 'customer_message' => $msg, 'admin_reply' => $reply]);
            }
        }
        return back();
    }

    public function settings()
    {
        $shared = $this->getSharedData();
        return view('pages.settings', array_merge($shared, [
            'pageId' => 'settings',
            'pageTitle' => 'App Settings — TOUCHSTAINS',
        ]));
    }

    public function menus()
    {
        $shared = $this->getSharedData();
        return view('pages.menus', array_merge($shared, [
            'pageId' => 'menus',
            'pageTitle' => 'All Menus — TOUCHSTAINS',
        ]));
    }

    public function onboarding()
    {
        $shared = $this->getSharedData();
        return view('pages.onboarding', array_merge($shared, [
            'pageId' => 'onboarding',
            'pageTitle' => 'Welcome to TOUCHSTAINS',
        ]));
    }

    public function login()
    {
        $shared = $this->getSharedData();
        return view('pages.login', array_merge($shared, [
            'pageId' => 'login',
            'pageTitle' => 'Login — TOUCHSTAINS',
        ]));
    }

    public function authenticate(Request $request)
    {
        $this->ensureAddressColumns();
        $phoneInput = trim((string)$request->input('phone', ''));
        $emailInput = trim((string)$request->input('email', ''));

        if ($phoneInput === '' && $emailInput === '') {
            $msg = 'Please enter your registered Mobile Number (or Email Address) to login.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->withInput()->with('error', $msg);
        }

        $cleanDigits = preg_replace('/\D+/', '', $phoneInput);
        $user = User::query()
            ->where(function ($q) use ($phoneInput, $emailInput, $cleanDigits) {
                if ($phoneInput !== '') {
                    $q->where('phone', $phoneInput)
                      ->orWhere('email', $phoneInput);
                    if (strlen($cleanDigits) >= 10) {
                        $last10 = substr($cleanDigits, -10);
                        $q->orWhere('phone', 'like', "%{$last10}");
                    }
                }
                if ($emailInput !== '') {
                    $q->orWhere('email', $emailInput);
                }
            })
            ->first();

        $redirectTarget = $request->input('redirect') === 'checkout' ? url('/checkout') : url('/');

        if (!$user) {
            $msg = 'No account found with this Mobile Number or Email. Please register first!';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $msg,
                ], 404);
            }
            return back()->withInput()->with('error', $msg);
        }

        Auth::login($user, true);
        Cookie::queue('touchstains_auth_uid', (string)$user->id, 525600); // Stay logged in 1 year until explicit logout

        $defaultAddr = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Welcome back, ' . $user->f_name . '!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'f_name' => $user->f_name,
                    'l_name' => $user->l_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'address' => $defaultAddr,
                'redirect' => $redirectTarget,
            ]);
        }
        return redirect($redirectTarget)->with('success', 'Welcome back, ' . $user->f_name . '! Your saved delivery address is ready.');
    }

    public function register()
    {
        $shared = $this->getSharedData();
        return view('pages.register', array_merge($shared, [
            'pageId' => 'register',
            'pageTitle' => 'Customer Registration — TOUCHSTAINS',
        ]));
    }

    public function storeUser(Request $request)
    {
        $this->ensureAddressColumns();
        $fName = trim((string)$request->input('f_name', ''));
        $lName = trim((string)$request->input('l_name', ''));
        $fullNameInput = trim((string)$request->input('full_name', ''));
        if ($fName === '' && $fullNameInput !== '') {
            $parts = preg_split('/\s+/', $fullNameInput, 2);
            $fName = $parts[0] ?? 'Customer';
            $lName = $parts[1] ?? '';
        }
        if ($fName === '') {
            $fName = 'Customer';
        }
        $fullName = trim($fName . ' ' . $lName);
        $phone = trim((string)$request->input('phone', ''));
        $cleanDigits = preg_replace('/\D+/', '', $phone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : ($cleanDigits ?: (string)rand(1000000000, 9999999999));
        $emailInput = trim((string)$request->input('email', ''));
        $email = $emailInput !== '' ? $emailInput : ('customer_' . $last10 . '@touchstains.com');

        $addressType = trim((string)$request->input('address_type', 'Home')) ?: 'Home';
        $contactPersonName = trim((string)$request->input('contact_person_name', '')) ?: $fullName;
        $streetAddress = trim((string)$request->input('address', ''));
        $pincode = trim((string)$request->input('pincode', ''));
        $road = trim((string)$request->input('road', ''));
        $house = trim((string)$request->input('house', ''));

        if ($phone === '' || $streetAddress === '') {
            $msg = 'Please fill in your Mobile Number and Full Delivery Address.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->withInput()->with('error', $msg);
        }

        if ($pincode !== '' && !str_contains($streetAddress, $pincode)) {
            $streetAddress .= ' - ' . $pincode;
        }

        // Look up existing customer by phone or email so re-registration updates smoothly without duplicate errors
        $user = User::where('phone', $phone)
            ->orWhere('phone', 'like', "%{$last10}")
            ->when($emailInput !== '', fn($q) => $q->orWhere('email', $emailInput))
            ->first();

        if ($user) {
            $updateData = [
                'name' => $fullName,
                'phone' => $phone,
            ];
            if ($emailInput !== '' && !User::where('email', $emailInput)->where('id', '!=', $user->id)->exists()) {
                $updateData['email'] = $emailInput;
            }
            $user->update($updateData);
        } else {
            if (User::where('email', $email)->exists()) {
                $email = 'customer_' . $last10 . '_' . rand(100, 999) . '@touchstains.com';
            }
            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)),
                'wallet_balance' => 100.00,
                'loyalty_points' => 50,
                'referral_code' => 'TOUCH' . rand(1000, 9999),
                'role' => 'customer',
            ]);
        }

        // Save customer's registration delivery address as their default delivery address
        Address::where('user_id', $user->id)->update(['is_default' => false]);
        $savedAddr = Address::create([
            'user_id' => $user->id,
            'address_type' => $addressType,
            'contact_person_name' => $contactPersonName,
            'contact_person_number' => $phone,
            'address' => $streetAddress,
            'road' => $road,
            'house' => $house,
            'floor' => $pincode,
            'latitude' => '19.0434',
            'longitude' => '72.8633',
            'is_default' => true,
        ]);

        Auth::login($user, true);
        Cookie::queue('touchstains_auth_uid', (string)$user->id, 525600); // Stay logged in 1 year until explicit logout

        $redirectTarget = $request->input('redirect') === 'checkout' ? url('/checkout') : url('/');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Registration successful! Your delivery address has been saved automatically.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'f_name' => $user->f_name,
                    'l_name' => $user->l_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'address' => $savedAddr,
                'redirect' => $redirectTarget,
            ]);
        }
        return redirect($redirectTarget)->with('success', 'Customer Registration completed! Your delivery address has been saved automatically.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Cookie::queue(Cookie::forget('touchstains_auth_uid'));
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/?logged_out=1')->with('success', 'You have been logged out successfully.');
    }

    public function forgotPassword()
    {
        $shared = $this->getSharedData();
        return view('pages.forgot-password', array_merge($shared, [
            'pageId' => 'forgot-password',
            'pageTitle' => 'Forgot Password — TOUCHSTAINS',
        ]));
    }

    public function about()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'about',
            'pageTitle' => 'About Us — TOUCHSTAINS',
            'policyHeading' => 'About TOUCHSTAINS',
            'policySubheading' => 'India\'s #1 Fabric Stain Remover & Fabric Care Solution (Shop No. 3 & 4, Braumonte, Sion, Mumbai)',
            'policyContent' => Setting::getValue('about_us'),
        ]));
    }

    public function contact()
    {
        $shared = $this->getSharedData();
        return view('pages.contact', array_merge($shared, [
            'pageId' => 'contact',
            'pageTitle' => 'Contact Us — TOUCHSTAINS',
        ]));
    }

    public function subscribeNewsletter(Request $request)
    {
        $email = trim($request->input('email', ''));
        if ($email !== '') {
            Newsletter::firstOrCreate(['email' => $email]);
        }
        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Subscribed successfully!']);
        }
        return back()->with('success', 'Subscribed to TOUCHSTAINS newsletter!');
    }

    public function faq()
    {
        $shared = $this->getSharedData();
        return view('pages.faq', array_merge($shared, [
            'pageId' => 'faq',
            'pageTitle' => 'Frequently Asked Questions — TOUCHSTAINS',
        ]));
    }

    public function terms()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'terms',
            'pageTitle' => 'Terms & Conditions — TOUCHSTAINS',
            'policyHeading' => 'Terms & Conditions',
            'policySubheading' => 'Official terms of service for TOUCHSTAINS (touchstains@gmail.com)',
            'policyContent' => Setting::getValue('terms_and_conditions'),
        ]));
    }

    public function privacyPolicy()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'privacy',
            'pageTitle' => 'Privacy Policy — TOUCHSTAINS',
            'policyHeading' => 'Privacy Policy',
            'policySubheading' => 'How TOUCHSTAINS protects and manages your personal data',
            'policyContent' => Setting::getValue('privacy_policy'),
        ]));
    }

    public function returnPolicy()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'return-policy',
            'pageTitle' => 'Return Policy — TOUCHSTAINS',
            'policyHeading' => 'Return Policy',
            'policySubheading' => 'Easy doorstep returns on eligible grocery items',
            'policyContent' => Setting::getValue('return_policy'),
        ]));
    }

    public function refundPolicy()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'refund-policy',
            'pageTitle' => 'Refund Policy — TOUCHSTAINS',
            'policyHeading' => 'Refund Policy',
            'policySubheading' => 'Fast wallet & original payment method refunds',
            'policyContent' => Setting::getValue('refund_policy'),
        ]));
    }

    public function cancellationPolicy()
    {
        $shared = $this->getSharedData();
        return view('pages.policy', array_merge($shared, [
            'pageId' => 'cancellation-policy',
            'pageTitle' => 'Cancellation Policy — TOUCHSTAINS',
            'policyHeading' => 'Cancellation Policy',
            'policySubheading' => 'Order cancellation rules before dispatch',
            'policyContent' => Setting::getValue('cancellation_policy'),
        ]));
    }

    /**
     * Handle any `/pages/{page}.html` link so static-style links also render via Laravel + SQLite.
     */
    public function legacyPage(string $page, Request $request)
    {
        return match ($page) {
            'categories' => $this->categories($request),
            'shop' => $this->shop($request),
            'flash-deals' => $this->flashDeals($request),
            'product' => $this->product($request->query('id'), $request),
            'cart' => $this->cart(),
            'checkout' => $this->checkout(),
            'order-success' => $this->orderSuccess($request),
            'orders' => $this->orders(),
            'order-details' => $this->orderDetails($request),
            'track-order' => $this->trackOrder($request),
            'wishlist' => $this->wishlist(),
            'wallet' => $this->wallet(),
            'coupons' => $this->coupons(),
            'address' => $this->addresses(),
            'account' => $this->account(),
            'refer' => $this->refer(),
            'notifications' => $this->notifications(),
            'chat' => $this->chat(),
            'settings' => $this->settings(),
            'menus' => $this->menus(),
            'onboarding' => $this->onboarding(),
            'login' => $this->login(),
            'register' => $this->register(),
            'forgot-password' => $this->forgotPassword(),
            'about' => $this->about(),
            'contact' => $this->contact(),
            'faq' => $this->faq(),
            'terms' => $this->terms(),
            'privacy', 'privacy-policy' => $this->privacyPolicy(),
            'return-policy' => $this->returnPolicy(),
            'refund-policy' => $this->refundPolicy(),
            'cancellation-policy' => $this->cancellationPolicy(),
            default => $this->home(),
        };
    }

    /**
     * Automatically dispatch confirmed orders to Shiprocket if auto-dispatch is enabled
     */
    protected function triggerShiprocketAutoDispatch(Order $order): void
    {
        try {
            $settings = \App\Models\DeliverySetting::getOrCreate();
            if ($settings->is_active && $settings->auto_shipment_on_confirm) {
                $deliveryManager = app(\App\Services\Delivery\DeliveryManager::class);
                $deliveryManager->processFullShipment($order);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Shiprocket Auto-Dispatch notice: ' . $e->getMessage());
        }
    }
}

