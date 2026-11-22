<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PriceSheetController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Api\ShiprocketWebhookController;
use App\Http\Middleware\AdminAuthMiddleware;

// ==========================================
// 1. STOREFRONT ROUTES (Laravel + SQLite)
// ==========================================
Route::get('/', [FrontController::class, 'home'])->name('home');
Route::get('/index.html', [FrontController::class, 'home']);

Route::get('/categories', [FrontController::class, 'categories'])->name('categories');
Route::get('/shop', [FrontController::class, 'shop'])->name('shop');
Route::get('/flash-deals', [FrontController::class, 'flashDeals'])->name('flash-deals');
Route::get('/product/{id?}', [FrontController::class, 'product'])->name('product');
Route::post('/product/{id}/review', [FrontController::class, 'storeReview'])->name('product.review');
Route::get('/product-details', [FrontController::class, 'product']);

Route::get('/cart', [FrontController::class, 'cart'])->name('cart');
Route::post('/coupon/apply', [FrontController::class, 'applyCoupon'])->name('coupon.apply');
Route::get('/checkout', [FrontController::class, 'checkout'])->name('checkout');
Route::post('/checkout/razorpay/create-order', [FrontController::class, 'createRazorpayOrder'])->name('checkout.razorpay.create-order');
Route::post('/checkout/place-order', [FrontController::class, 'placeOrder'])->name('checkout.place-order');
Route::post('/checkout/place-upi-order', [FrontController::class, 'placeUpiOrder'])->name('checkout.place-upi-order');
Route::get('/order-upi-payment/{id}', [FrontController::class, 'upiPaymentPage'])->name('order.upi-payment');
Route::post('/order-upi-payment/{id}/confirm', [FrontController::class, 'confirmUpiPayment'])->name('order.upi-payment.confirm');
Route::post('/checkout/create-razorpay-order', [FrontController::class, 'createRazorpayOrder'])->name('checkout.create-razorpay-order');
Route::post('/checkout/verify-razorpay-payment', [FrontController::class, 'verifyRazorpayPayment'])->name('checkout.verify-razorpay-payment');
Route::post('/checkout/razorpay/webhook', [FrontController::class, 'handleRazorpayWebhook'])->name('razorpay.webhook');
Route::post('/payment/bank-transfer/submit', [FrontController::class, 'submitBankTransfer'])->name('payment.bank-transfer.submit');
Route::get('/order-success/{id?}', [FrontController::class, 'orderSuccess'])->name('order-success');

Route::get('/orders', [FrontController::class, 'orders'])->name('orders');
Route::get('/order-details/{id?}', [FrontController::class, 'orderDetails'])->name('order-details');
Route::post('/orders/{id}/cancel', [FrontController::class, 'cancelOrder'])->name('orders.cancel');
Route::get('/track-order', [FrontController::class, 'trackOrder'])->name('track-order');
Route::get('/verify-order/{id?}', [FrontController::class, 'verifyOrder'])->name('verify-order');
Route::get('/order-verify/{id?}', [FrontController::class, 'verifyOrder']);
Route::get('/verify/package/{id?}', [FrontController::class, 'verifyOrder']);
Route::get('/v/{id?}', [FrontController::class, 'verifyOrder'])->name('v');
Route::get('/scan/order/{token?}', [FrontController::class, 'scanOrder'])->name('scan.order');
Route::get('/scan/package/{token?}', [FrontController::class, 'scanOrder']);
Route::get('/scan/product/{id?}', [FrontController::class, 'scanProduct'])->name('scan.product');
Route::get('/p/{id?}', [FrontController::class, 'scanProduct'])->name('product.scan');

Route::get('/wishlist', [FrontController::class, 'wishlist'])->name('wishlist');
Route::post('/wishlist/toggle', [FrontController::class, 'toggleWishlist'])->name('wishlist.toggle');

Route::redirect('/wallet', '/account');
Route::redirect('/coupons', '/');
Route::redirect('/refer', '/account');

Route::get('/address', [FrontController::class, 'addresses'])->name('address');
Route::post('/address', [FrontController::class, 'storeAddress'])->name('address.store');
Route::post('/address/{id}/delete', [FrontController::class, 'deleteAddress'])->name('address.delete');

Route::get('/account', [FrontController::class, 'account'])->name('account');
Route::post('/account/update', [FrontController::class, 'updateProfile'])->name('account.update');

Route::get('/notifications', [FrontController::class, 'notifications'])->name('notifications');

Route::get('/chat', [FrontController::class, 'chat'])->name('chat');
Route::post('/chat/send', [FrontController::class, 'sendMessage'])->name('chat.send');

Route::get('/settings', [FrontController::class, 'settings'])->name('settings');
Route::get('/menus', [FrontController::class, 'menus'])->name('menus');
Route::get('/onboarding', [FrontController::class, 'onboarding'])->name('onboarding');

// Auth Routes
Route::get('/login', [FrontController::class, 'login'])->name('login');
Route::post('/login', [FrontController::class, 'authenticate']);
Route::get('/register', [FrontController::class, 'register'])->name('register');
Route::post('/register', [FrontController::class, 'storeUser']);
Route::get('/logout', [FrontController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [FrontController::class, 'forgotPassword'])->name('forgot-password');

// Info & Policy Routes (Loaded from SQLite `settings`)
Route::redirect('/about', '/contact');
Route::get('/contact', [FrontController::class, 'contact'])->name('contact');
Route::post('/newsletter/subscribe', [FrontController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::get('/faq', [FrontController::class, 'faq'])->name('faq');
Route::get('/terms', [FrontController::class, 'terms'])->name('terms');
Route::get('/privacy', [FrontController::class, 'privacyPolicy']);
Route::get('/privacy-policy', [FrontController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/return-policy', [FrontController::class, 'returnPolicy'])->name('return-policy');
Route::get('/refund-policy', [FrontController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/cancellation-policy', [FrontController::class, 'cancellationPolicy'])->name('cancellation-policy');

// Support `/pages/{page}.html` and `/pages/{page}` aliases
Route::get('/pages/{page}.html', [FrontController::class, 'legacyPage']);
Route::get('/pages/{page}', [FrontController::class, 'legacyPage']);

// ==========================================
// 2. ADMIN AUTHENTICATION ROUTES
// ==========================================
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate'])->name('admin.authenticate');
    Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLink'])->name('admin.send-reset');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/logout', [AdminAuthController::class, 'logout']);
});

// ==========================================
// 2.5 SUPER ADMIN PROTECTED ROUTES
// ==========================================
Route::prefix('admin')->middleware([AdminAuthMiddleware::class])->group(function () {
    
    // Dashboard Overview
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Products Management
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('/products/{id}/update', [ProductController::class, 'update'])->name('admin.products.update');
    Route::post('/products/{id}/delete-image', [ProductController::class, 'deleteImage'])->name('admin.products.delete-image');
    Route::post('/products/{id}/delete', [ProductController::class, 'destroy'])->name('admin.products.delete');
    Route::get('/products/export/csv', [ProductController::class, 'exportCsv'])->name('admin.products.export');

    // Categories & Brands Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('admin.categories.delete');
    Route::get('/brands', [CategoryController::class, 'brands'])->name('admin.brands');
    Route::post('/brands', [CategoryController::class, 'storeBrand'])->name('admin.brands.store');
    Route::post('/brands/{id}/delete', [CategoryController::class, 'destroyBrand'])->name('admin.brands.delete');

    // Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice'])->name('admin.orders.invoice');
    Route::post('/orders/{id}/delete', [OrderController::class, 'destroy'])->name('admin.orders.delete');
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    Route::post('/orders/bulk-status/apply', [OrderController::class, 'bulkStatus'])->name('admin.orders.bulk-status');

    // Customer Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('admin.customers.store');
    Route::post('/customers/{id}/update', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::post('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('admin.customers.toggle-status');
    Route::post('/customers/{id}/delete', [CustomerController::class, 'destroy'])->name('admin.customers.delete');
    Route::get('/customers/export/csv', [CustomerController::class, 'exportCsv'])->name('admin.customers.export');

    // Bulk Price Sheet System
    Route::get('/price-sheet', [PriceSheetController::class, 'index'])->name('admin.price-sheet');
    Route::post('/price-sheet/{id}/quick-update', [PriceSheetController::class, 'quickUpdate'])->name('admin.price-sheet.quick-update');
    Route::post('/price-sheet/bulk-save', [PriceSheetController::class, 'bulkSave'])->name('admin.price-sheet.bulk-save');
    Route::get('/price-sheet/export/csv', [PriceSheetController::class, 'exportCsv'])->name('admin.price-sheet.export');
    Route::post('/price-sheet/import/csv', [PriceSheetController::class, 'importCsv'])->name('admin.price-sheet.import');

    // Inventory Stock Management
    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory');
    Route::post('/inventory/{id}/adjust', [InventoryController::class, 'adjustStock'])->name('admin.inventory.adjust');

    // Payments & AU Bank Transfer Reconcile
    Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments');
    Route::post('/payments/toggle-method', [PaymentController::class, 'toggleMethod'])->name('admin.payments.toggle-method');
    Route::post('/payments/update-upi', [PaymentController::class, 'updateUpiSettings'])->name('admin.payments.update-upi');
    Route::post('/payments/delete-upi-qr', [PaymentController::class, 'deleteUpiQr'])->name('admin.payments.delete-upi-qr');
    Route::post('/payments/{id}/approve', [PaymentController::class, 'approve'])->name('admin.payments.approve');
    Route::post('/payments/{id}/reject', [PaymentController::class, 'reject'])->name('admin.payments.reject');
    Route::post('/bank-transfers/{id}/approve', [PaymentController::class, 'approve'])->name('admin.bank-transfers.approve');
    Route::post('/bank-transfers/{id}/reject', [PaymentController::class, 'reject'])->name('admin.bank-transfers.reject');
    Route::post('/bank-transfers/{id}/note', [AdminController::class, 'noteBankTransfer'])->name('admin.bank-transfers.note');
    Route::post('/bank-account/update', [AdminController::class, 'updateBankAccount'])->name('admin.bank-account.update');

    // Marketing & Coupons
    Route::get('/coupons', [MarketingController::class, 'coupons'])->name('admin.coupons');
    Route::post('/coupons', [MarketingController::class, 'storeCoupon'])->name('admin.coupons.store');
    Route::post('/coupons/{id}/delete', [MarketingController::class, 'destroyCoupon'])->name('admin.coupons.delete');

    // Website CMS, Banners, Live Demo Videos & Enquiries
    Route::get('/banners', [CmsController::class, 'banners'])->name('admin.banners');
    Route::post('/banners', [CmsController::class, 'storeBanner'])->name('admin.banners.store');
    Route::post('/banners/{id}/update', [CmsController::class, 'updateBanner'])->name('admin.banners.update');
    Route::post('/banners/{id}/delete', [CmsController::class, 'destroyBanner'])->name('admin.banners.delete');
    Route::get('/demo-videos', [CmsController::class, 'demoVideos'])->name('admin.demo-videos');
    Route::post('/demo-videos', [CmsController::class, 'storeDemoVideo'])->name('admin.demo-videos.store');
    Route::post('/demo-videos/{id}/update', [CmsController::class, 'updateDemoVideo'])->name('admin.demo-videos.update');
    Route::post('/demo-videos/{id}/toggle-status', [CmsController::class, 'toggleDemoVideoStatus'])->name('admin.demo-videos.toggle');
    Route::post('/demo-videos/{id}/delete', [CmsController::class, 'destroyDemoVideo'])->name('admin.demo-videos.delete');
    Route::get('/cms', [CmsController::class, 'cms'])->name('admin.cms');
    Route::post('/cms/{id}/update', [CmsController::class, 'updateCmsPage'])->name('admin.cms.update');
    Route::get('/enquiries', [CmsController::class, 'enquiries'])->name('admin.enquiries');

    // Reports & Financial Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');

    // Staff & Security Audit Trail
    Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff');
    Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::get('/audit-logs', [StaffController::class, 'auditLogs'])->name('admin.audit-logs');

    // Profile & Website Settings
    Route::get('/profile', [ProfileController::class, 'profile'])->name('admin.profile');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('admin.profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('admin.settings.update');

    // ==========================================
    // SHIPROCKET DELIVERY MANAGEMENT ROUTES
    // ==========================================
    Route::get('/delivery', [DeliveryController::class, 'dashboard'])->name('admin.delivery.dashboard');
    Route::get('/delivery/settings', [DeliveryController::class, 'settings'])->name('admin.delivery.settings');
    Route::post('/delivery/settings', [DeliveryController::class, 'updateSettings'])->name('admin.delivery.settings.update');
    Route::post('/delivery/test-connection', [DeliveryController::class, 'testConnection'])->name('admin.delivery.test-connection');
    Route::post('/delivery/register-pickup', [DeliveryController::class, 'registerPickup'])->name('admin.delivery.register-pickup');
    Route::get('/delivery/check-serviceability', [DeliveryController::class, 'checkServiceability'])->name('admin.delivery.check-serviceability');
    Route::post('/delivery/orders/{orderId}/create-shipment', [DeliveryController::class, 'createShipment'])->name('admin.delivery.orders.create-shipment');
    Route::post('/delivery/shipments/{shipmentId}/generate-awb', [DeliveryController::class, 'generateAwb'])->name('admin.delivery.shipments.generate-awb');
    Route::get('/delivery/shipments/{shipmentId}/generate-label', [DeliveryController::class, 'generateLabel'])->name('admin.delivery.shipments.generate-label');
    Route::get('/delivery/shipments/{shipmentId}/print-label', [DeliveryController::class, 'printLabel'])->name('admin.delivery.shipments.print-label');
    Route::get('/delivery/shipments/{shipmentId}/print-manifest', [DeliveryController::class, 'printManifest'])->name('admin.delivery.shipments.print-manifest');
    Route::post('/delivery/shipments/{shipmentId}/schedule-pickup', [DeliveryController::class, 'schedulePickup'])->name('admin.delivery.shipments.schedule-pickup');
    Route::post('/delivery/shipments/{shipmentId}/track', [DeliveryController::class, 'trackShipment'])->name('admin.delivery.shipments.track');
    Route::post('/delivery/shipments/{shipmentId}/cancel', [DeliveryController::class, 'cancelShipment'])->name('admin.delivery.shipments.cancel');
    Route::post('/delivery/shipments/{shipmentId}/return', [DeliveryController::class, 'createReturn'])->name('admin.delivery.shipments.return');
    Route::post('/delivery/sync-tracking', [DeliveryController::class, 'syncAllTracking'])->name('admin.delivery.sync-tracking');
    Route::get('/delivery/export', [DeliveryController::class, 'exportShipments'])->name('admin.delivery.export');
});

// Shiprocket Live Tracking Inbound Webhook
Route::post('/api/v1/delivery/webhook/shiprocket', [ShiprocketWebhookController::class, 'handle'])->name('delivery.webhook.shiprocket');

// ==========================================
// 3. GROFRESH v7.7 REST API ENDPOINTS (SQLite)
// ==========================================
foreach (['api/v1', 'app/api/v1'] as $prefix) {
    Route::prefix($prefix)->group(function () {
        Route::get('/config', [ApiController::class, 'config']);
        Route::get('/bank-account', [ApiController::class, 'bankAccount']);
        Route::post('/bank-transfer/submit', [ApiController::class, 'submitBankTransfer']);
        Route::get('/categories', [ApiController::class, 'categories']);
        Route::get('/categories/childes/{id}', [ApiController::class, 'childCategories']);
        Route::get('/categories/products/{id}', [ApiController::class, 'categoryProducts']);
        Route::get('/banners', [ApiController::class, 'banners']);
        Route::get('/products/all', [ApiController::class, 'allProducts']);
        Route::get('/products/latest', [ApiController::class, 'allProducts']);
        Route::get('/products/daily-needs', [ApiController::class, 'dailyNeeds']);
        Route::get('/products/featured', [ApiController::class, 'featuredProducts']);
        Route::get('/products/most-reviewed', [ApiController::class, 'mostReviewed']);
        Route::get('/products/details/{id}', [ApiController::class, 'productDetails']);
        Route::get('/coupon/list', [ApiController::class, 'coupons']);
        Route::get('/customer/order/list', [ApiController::class, 'orders']);
    });
}

// ==========================================
// 4. STORAGE & VIDEO FALLBACK ROUTES (For Shared Hosting without Symlink)
// ==========================================
Route::get('/storage/{path}', function ($path) {
    foreach ([
        public_path('storage/' . $path),
        storage_path('app/public/' . $path),
        base_path('storage/' . $path),
    ] as $filePath) {
        if (is_file($filePath)) {
            return response()->file($filePath);
        }
    }
    abort(404);
})->where('path', '.*');

Route::get('/videos/{filename}', function ($filename) {
    $safeName = basename($filename);
    foreach ([public_path('videos/' . $safeName), base_path('videos/' . $safeName)] as $filePath) {
        if (file_exists($filePath)) {
            return response()->file($filePath);
        }
    }
    abort(404);
})->where('filename', '[A-Za-z0-9._-]+');

