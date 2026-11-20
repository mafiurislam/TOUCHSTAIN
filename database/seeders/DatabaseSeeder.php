<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/full_site_data.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('full_site_data.json');
        }
        $data = json_decode(file_get_contents($jsonPath), true);
        $config = $data['config'] ?? [];

        DB::transaction(function () use ($data, $config) {
            // 1. Seed Users (Customer & Admin)
            $customer = User::updateOrCreate(
                ['email' => 'touchstains@gmail.com'],
                [
                    'name' => 'TOUCHSTAINS Customer',
                    'phone' => '+91 7679416294 / 7039214465',
                    'password' => Hash::make('password'),
                    'wallet_balance' => 120.00,
                    'loyalty_points' => 35,
                    'referral_code' => 'TOUCHSTAINS',
                    'role' => 'customer',
                ]
            );

            User::updateOrCreate(
                ['email' => 'touchstains@gmail.com'],
                [
                    'name' => 'TOUCHSTAINS Admin (Mumbai)',
                    'phone' => '+91 7679416294 / 7039214465',
                    'password' => Hash::make('admin123'),
                    'wallet_balance' => 500.00,
                    'loyalty_points' => 100,
                    'referral_code' => 'ADMIN732201',
                    'role' => 'admin',
                ]
            );

            // 2. Seed Settings
            foreach ($config as $k => $v) {
                Setting::updateOrCreate(
                    ['key' => $k],
                    ['value' => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v]
                );
            }

            // 3. Seed Branches
            foreach (($config['branches'] ?? []) as $br) {
                Branch::updateOrCreate(
                    ['id' => $br['id']],
                    [
                        'name' => $br['name'] ?? 'Jadupr',
                        'email' => $br['email'] ?? 'touchstains@gmail.com',
                        'address' => $br['address'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022',
                        'latitude' => (string) ($br['latitude'] ?? '24.878860727271434'),
                        'longitude' => (string) ($br['longitude'] ?? '88.04068702273109'),
                        'coverage' => (int) ($br['coverage'] ?? 2),
                        'status' => (bool) ($br['status'] ?? 1),
                    ]
                );
            }

            // 4. Seed Categories & Child Subcategories
            $catDetails = $data['catDetails'] ?? [];
            foreach ($catDetails as $catId => $catObj) {
                Category::updateOrCreate(
                    ['id' => (int) $catObj['id']],
                    [
                        'parent_id' => 0,
                        'name' => $catObj['name'],
                        'slug' => Str::slug($catObj['name']),
                        'image' => $catObj['image'] ?? null,
                        'position' => (int) ($catObj['position'] ?? 0),
                        'status' => true,
                    ]
                );

                foreach (($catObj['childes'] ?? []) as $child) {
                    Category::updateOrCreate(
                        ['id' => (int) $child['id']],
                        [
                            'parent_id' => (int) $catObj['id'],
                            'name' => $child['name'],
                            'slug' => Str::slug($child['name']),
                            'image' => $child['image'] ?? ($catObj['image'] ?? null),
                            'position' => (int) ($child['position'] ?? 1),
                            'status' => true,
                        ]
                    );
                }
            }

            // 5. Seed Banners
            foreach (($data['banners'] ?? []) as $b) {
                Banner::updateOrCreate(
                    ['id' => (int) $b['id']],
                    [
                        'title' => $b['title'] ?? 'Special Offer',
                        'image' => $b['image'],
                        'product_id' => $b['product_id'] ?? null,
                        'category_id' => $b['category_id'] ?? 20,
                        'status' => (bool) ($b['status'] ?? 1),
                    ]
                );
            }

            // 6. Seed All 240 Real Products
            $dailyNeedIds = collect($data['dailyNeedsProducts'] ?? [])->pluck('id')->map(fn($id) => (int) $id)->flip();
            $featuredIds = collect($data['featuredProducts'] ?? [])->pluck('id')->map(fn($id) => (int) $id)->flip();

            foreach (($data['allProducts'] ?? []) as $p) {
                $catIds = $p['category_ids'] ?? [];
                $primaryCatId = isset($catIds[0]['id']) ? (int) $catIds[0]['id'] : null;
                $subCatId = isset($catIds[1]['id']) ? (int) $catIds[1]['id'] : null;
                $pid = (int) $p['id'];

                Product::updateOrCreate(
                    ['id' => $pid],
                    [
                        'name' => $p['name'],
                        'description' => $p['description'] ?? null,
                        'image' => is_array($p['image'] ?? null) ? $p['image'] : [$p['image'] ?? 'place_holder.png'],
                        'price' => (float) ($p['price'] ?? 0),
                        'discount' => (float) ($p['discount'] ?? 0),
                        'discount_type' => $p['discount_type'] ?? 'percent',
                        'tax' => (float) ($p['tax'] ?? 0),
                        'tax_type' => $p['tax_type'] ?? 'percent',
                        'unit' => $p['unit'] ?? 'pc',
                        'capacity' => (float) ($p['capacity'] ?? 1),
                        'total_stock' => (int) ($p['total_stock'] ?? 0),
                        'daily_needs' => $dailyNeedIds->has($pid) || !empty($p['daily_needs']),
                        'is_featured' => $featuredIds->has($pid) || !empty($p['is_featured']),
                        'popularity_count' => (int) ($p['popularity_count'] ?? 0),
                        'view_count' => (int) ($p['view_count'] ?? 0),
                        'maximum_order_quantity' => (int) ($p['maximum_order_quantity'] ?? 10),
                        'weight' => (float) ($p['weight'] ?? 0),
                        'category_id' => $primaryCatId,
                        'sub_category_id' => $subCatId,
                        'category_ids' => $catIds,
                        'variations' => $p['variations'] ?? [],
                        'choice_options' => $p['choice_options'] ?? [],
                        'status' => true,
                    ]
                );
            }

            // 7. Seed Coupons
            $coupons = [
                ['code' => 'GETGOZO10', 'title' => 'Flat ₹10 OFF on orders ≥ ₹69', 'discount' => 10, 'discount_type' => 'amount', 'min_purchase' => 69, 'expire_date' => '2027-12-31'],
                ['code' => 'WELCOME20', 'title' => 'Welcome Offer Flat ₹20 OFF', 'discount' => 20, 'discount_type' => 'amount', 'min_purchase' => 149, 'expire_date' => '2027-12-31'],
                ['code' => 'TOUCH20', 'title' => 'TOUCHSTAINS Special ₹20 OFF', 'discount' => 20, 'discount_type' => 'amount', 'min_purchase' => 149, 'expire_date' => '2027-12-31'],
            ];
            foreach ($coupons as $cp) {
                Coupon::updateOrCreate(['code' => $cp['code']], $cp);
            }

            // 8. Seed Default Address
            Address::updateOrCreate(
                ['id' => 1],
                [
                    'user_id' => $customer->id,
                    'address_type' => 'Home',
                    'contact_person_name' => 'TOUCHSTAINS Customer',
                    'contact_person_number' => '+91 7679416294 / 7039214465',
                    'address' => 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022',
                    'latitude' => '24.878860727271434',
                    'longitude' => '88.04068702273109',
                    'branch_id' => 1,
                ]
            );

            // 9. Seed Sample Order (#100248)
            $sampleOrder = Order::updateOrCreate(
                ['id' => 100248],
                [
                    'user_id' => $customer->id,
                    'branch_id' => 1,
                    'order_amount' => 180.00,
                    'subtotal' => 180.00,
                    'product_discount' => 29.00,
                    'coupon_discount_amount' => 0.00,
                    'delivery_charge' => 0.00,
                    'payment_method' => 'Cash on Delivery',
                    'payment_status' => 'paid',
                    'order_status' => 'delivered',
                    'order_type' => 'Home Delivery',
                    'delivery_address' => 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022',
                    'contact_person_name' => 'TOUCHSTAINS Customer',
                    'contact_person_number' => '+91 7679416294 / 7039214465',
                    'time_slot' => 'Today (Within 45-60 mins)',
                    'earned_loyalty_points' => 9,
                ]
            );

            OrderItem::updateOrCreate(
                ['order_id' => $sampleOrder->id, 'product_id' => 351],
                [
                    'product_name' => 'Fresh Orange (Sweet & Juicy)',
                    'product_image' => '2026-10-27-6ae00fde916f7.png',
                    'unit' => '500 gm',
                    'price' => 50.00,
                    'old_price' => 60.00,
                    'quantity' => 1,
                    'discount_on_product' => 10.00,
                ]
            );
            OrderItem::updateOrCreate(
                ['order_id' => $sampleOrder->id, 'product_id' => 348],
                [
                    'product_name' => 'Britannia Marie Gold Biscuit',
                    'product_image' => '2026-09-11-6aa3f2fe96eaa.png',
                    'unit' => '384 gm',
                    'price' => 50.00,
                    'old_price' => 50.00,
                    'quantity' => 1,
                    'discount_on_product' => 0.00,
                ]
            );
            OrderItem::updateOrCreate(
                ['order_id' => $sampleOrder->id, 'product_id' => 347],
                [
                    'product_name' => 'Neha Hand Smile Liquid Hand Wash',
                    'product_image' => '2026-09-11-6aa3f27d54dc8.png',
                    'unit' => '750 ml',
                    'price' => 80.00,
                    'old_price' => 99.00,
                    'quantity' => 1,
                    'discount_on_product' => 19.00,
                ]
            );

            // 10. Seed Welcome Support Message
            Message::firstOrCreate(
                ['id' => 1],
                [
                    'user_id' => $customer->id,
                    'sender' => 'admin',
                    'message' => 'Hello! Welcome to TOUCHSTAINS Customer Support (Shop No. 3 & 4, Tower B, Braumonte, Sion, Mumbai). Call +91 7679416294 / 7039214465. How can we help you today?',
                    'is_read' => true,
                ]
            );
        });
    }
}
