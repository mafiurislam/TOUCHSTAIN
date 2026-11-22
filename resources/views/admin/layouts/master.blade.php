<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Dashboard') — TOUCHSTAINS TradeHub</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Google Fonts & Chart.js -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sidebar-bg: #2563EB;
            --sidebar-active: #1D4ED8;
            --sidebar-hover: #3B82F6;
            --sidebar-text: #E0E7FF;
            --sidebar-text-muted: #93C5FD;
            --body-bg: #F1F5F9;
            --card-bg: #FFFFFF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --primary: #2563EB;
            --primary-light: #EFF6FF;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --border: #E2E8F0;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px -2px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: var(--body-bg); color: var(--text-dark); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* Left Collapsible Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .sidebar-brand {
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-brand-icon {
            width: 38px;
            height: 38px;
            background: #06B6D4;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(6,182,212,0.4);
        }
        .sidebar-brand-name {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.4px;
        }

        .sidebar-menu {
            padding: 16px 14px 40px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sidebar-heading {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--sidebar-text-muted);
            padding: 14px 12px 6px;
            margin-top: 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .nav-link-left {
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .nav-link.active {
            background: rgba(255,255,255,0.22);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .nav-badge {
            background: #06B6D4;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 999px;
        }
        .nav-badge-danger {
            background: #EF4444;
        }

        /* Main Content Canvas */
        .main-wrapper {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Top Header Navbar */
        .topbar {
            height: 72px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 99;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .sidebar-toggle-btn {
            background: none;
            border: 1px solid var(--border);
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            display: none;
        }
        .welcome-text {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--text-dark);
        }
        .welcome-date {
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .search-container {
            flex: 1;
            max-width: 420px;
            margin: 0 24px;
            position: relative;
        }
        .search-input {
            width: 100%;
            background: #F8FAFC;
            border: 1.5px solid var(--border);
            border-radius: 999px;
            padding: 9px 18px 9px 38px;
            font-size: 13px;
            color: var(--text-dark);
            transition: all 0.2s;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .icon-btn {
            position: relative;
            background: #F8FAFC;
            border: 1px solid var(--border);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-dark);
            text-decoration: none;
            transition: background 0.2s;
        }
        .icon-btn:hover { background: #E2E8F0; }
        .icon-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #EF4444;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .user-dropdown {
            position: relative;
            cursor: pointer;
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: #fff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }

        /* Content Area */
        .page-content {
            padding: 24px 28px 60px;
            flex: 1;
        }

        /* Generic Admin Cards */
        .admin-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
        }
        .admin-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 14px;
        }
        .admin-card-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Buttons */
        .btn-theme {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.25);
        }
        .btn-theme:hover { background: #1D4ED8; color: #fff; transform: translateY(-1px); }
        .btn-secondary {
            background: #F1F5F9;
            color: var(--text-dark);
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secondary:hover { background: #E2E8F0; }

        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }
        .admin-table th {
            background: #F8FAFC;
            padding: 12px 14px;
            font-weight: 700;
            color: #475569;
            border-bottom: 1.5px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        .admin-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: #F8FAFC;
        }

        /* Status Pills */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-success { background: #DCFCE7; color: #166534; }
        .status-warning { background: #FEF3C7; color: #92400E; }
        .status-info { background: #DBEAFE; color: #1E40AF; }
        .status-danger { background: #FEE2E2; color: #991B1B; }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; width: 100%; }
            .sidebar-toggle-btn { display: inline-flex; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Left Collapsible Sidebar (TradeHub Blue Theme) -->
    <aside class="sidebar" id="adminSidebar">
        
        <div class="sidebar-brand">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:42px;width:auto;object-fit:contain;background:#fff;padding:3px 6px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2)">
            <div>
                <div class="sidebar-brand-name" style="font-size:16px">TOUCHSTAINS</div>
                <div style="font-size:11px;color:var(--sidebar-text-muted)">Super Admin v2.4</div>
            </div>
        </div>

        <nav class="sidebar-menu">
            
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>📊</span>
                    <span>Dashboard</span>
                </span>
                <span class="nav-badge">Live</span>
            </a>

            <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>📈</span>
                    <span>Reports & Sales</span>
                </span>
            </a>

            <a href="{{ route('admin.customers') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>👥</span>
                    <span>Customers</span>
                </span>
            </a>

            <a href="{{ route('admin.coupons') }}" class="nav-link {{ request()->is('admin/coupons*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🎟️</span>
                    <span>Marketing & Coupons</span>
                </span>
            </a>

            <div class="sidebar-heading">Stock & Orders</div>

            <a href="{{ route('admin.products') }}" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>📦</span>
                    <span>Products</span>
                </span>
                <span class="nav-badge" style="background:#3B82F6">{{ \App\Models\Product::count() }}</span>
            </a>

            <a href="{{ route('admin.categories') }}" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🗂️</span>
                    <span>Categories</span>
                </span>
            </a>

            <a href="{{ route('admin.brands') }}" class="nav-link {{ request()->is('admin/brands*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🏷️</span>
                    <span>Brands</span>
                </span>
            </a>

            <a href="{{ route('admin.price-sheet') }}" class="nav-link {{ request()->is('admin/price-sheet*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>📋</span>
                    <span>Price Sheet</span>
                </span>
                <span class="nav-badge" style="background:#10B981">Bulk</span>
            </a>

            <a href="{{ route('admin.inventory') }}" class="nav-link {{ request()->is('admin/inventory*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🏢</span>
                    <span>Inventory Logs</span>
                </span>
            </a>

            <a href="{{ route('admin.orders') }}" class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🧾</span>
                    <span>Orders</span>
                </span>
                @php
                    $pendingOrdCount = \App\Models\Order::where('order_status', 'pending')->count();
                @endphp
                @if($pendingOrdCount > 0)
                    <span class="nav-badge nav-badge-danger">{{ $pendingOrdCount }}</span>
                @endif
            </a>

            <a href="{{ url('/admin/delivery') }}" class="nav-link {{ request()->is('admin/delivery*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🚚</span>
                    <span>Delivery (Shiprocket)</span>
                </span>
                <span class="nav-badge" style="background:#059669">Live</span>
            </a>

            <a href="{{ route('admin.payments') }}" class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>💳</span>
                    <span>Bank & Payments</span>
                </span>
            </a>

            <div class="sidebar-heading">Content & CMS</div>

            <a href="{{ route('admin.demo-videos') }}" class="nav-link {{ request()->is('admin/demo-videos*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🎬</span>
                    <span>Live Demo Videos</span>
                </span>
                <span class="nav-badge" style="background:#EF4444">Live</span>
            </a>

            <a href="{{ route('admin.banners') }}" class="nav-link {{ request()->is('admin/banners*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🖼️</span>
                    <span>Banners & Sliders</span>
                </span>
            </a>

            <a href="{{ route('admin.cms') }}" class="nav-link {{ request()->is('admin/cms*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>📄</span>
                    <span>Website CMS Pages</span>
                </span>
            </a>

            <a href="{{ route('admin.enquiries') }}" class="nav-link {{ request()->is('admin/enquiries*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>💬</span>
                    <span>Messages & Chat</span>
                </span>
            </a>

            <div class="sidebar-heading">Settings & Security</div>

            <a href="{{ route('admin.staff') }}" class="nav-link {{ request()->is('admin/staff*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🛡️</span>
                    <span>Staff & Roles</span>
                </span>
            </a>

            <a href="{{ route('admin.audit-logs') }}" class="nav-link {{ request()->is('admin/audit-logs*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>🔒</span>
                    <span>Audit & Security</span>
                </span>
            </a>

            <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <span class="nav-link-left">
                    <span>⚙️</span>
                    <span>Store Settings</span>
                </span>
            </a>

            <a href="{{ url('/') }}" target="_blank" class="nav-link" style="margin-top:14px;border:1px dashed rgba(255,255,255,0.3)">
                <span class="nav-link-left">
                    <span>🌐</span>
                    <span>View Live Store ↗</span>
                </span>
            </a>

        </nav>
    </aside>

    <!-- Main Content Canvas -->
    <div class="main-wrapper">
        
        <!-- Top Navigation Bar -->
        <header class="topbar">
            
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
                    ☰
                </button>
                <div>
                    <div class="welcome-text">
                        Welcome, {{ auth()->user()->name ?? 'Super Admin' }}
                    </div>
                    <div class="welcome-date" id="liveDateDisplay">
                        {{ date('jS F, Y | l') }}
                    </div>
                </div>
            </div>

            <!-- Global Search -->
            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" id="globalAdminSearch" class="search-input" placeholder="Type here to search products, orders, customers..." onkeyup="handleGlobalSearch(event)">
            </div>

            <div class="topbar-right">
                
                <!-- Notification Bell -->
                <a href="{{ route('admin.orders') }}" class="icon-btn" title="Pending Orders">
                    <span>🔔</span>
                    <span class="icon-badge">{{ $pendingOrdCount ?? 0 }}</span>
                </a>

                <!-- Messages -->
                <a href="{{ route('admin.enquiries') }}" class="icon-btn" title="Customer Enquiries">
                    <span>✉️</span>
                    <span class="icon-badge" style="background:#3B82F6">3</span>
                </a>

                <!-- Profile Dropdown -->
                <div class="user-dropdown" onclick="toggleProfileMenu()">
                    <div class="user-avatar" title="{{ auth()->user()->name ?? 'Admin' }}">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div id="profileDropdownMenu" style="display:none;position:absolute;right:0;top:48px;background:#fff;border:1px solid var(--border);border-radius:12px;width:210px;box-shadow:var(--shadow-lg);padding:8px;z-index:999">
                        <div style="padding:8px 12px;border-bottom:1px solid #F1F5F9;margin-bottom:6px">
                            <div style="font-weight:700;font-size:13px">{{ auth()->user()->name ?? 'Admin' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ auth()->user()->email ?? 'admin@12345' }}</div>
                        </div>
                        <a href="{{ route('admin.profile') }}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:13px;color:var(--text-dark);text-decoration:none;border-radius:6px" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='transparent'">
                            👤 Admin Profile
                        </a>
                        <a href="{{ route('admin.settings') }}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:13px;color:var(--text-dark);text-decoration:none;border-radius:6px" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='transparent'">
                            ⚙️ Preferences
                        </a>
                        <a href="{{ url('/') }}" target="_blank" style="display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:13px;color:var(--text-dark);text-decoration:none;border-radius:6px" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='transparent'">
                            🌐 Storefront
                        </a>
                        <div style="border-top:1px solid #F1F5F9;margin:6px 0"></div>
                        <form action="{{ route('admin.logout') }}" method="POST" style="margin:0">
                            @csrf
                            <button type="submit" style="width:100%;text-align:left;display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:13px;color:#DC2626;background:none;border:none;cursor:pointer;border-radius:6px" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'">
                                🚪 Log Out
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </header>

        <!-- Page Main Canvas -->
        <main class="page-content">

            @if(session('success'))
                <div style="background:#DCFCE7;border:1px solid #86EFAC;color:#166534;padding:14px 18px;border-radius:12px;font-weight:700;font-size:13.5px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between">
                    <div>✅ {{ session('success') }}</div>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:800;color:#166534">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:14px 18px;border-radius:12px;font-weight:700;font-size:13.5px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between">
                    <div>❌ {{ session('error') }}</div>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:800;color:#991B1B">&times;</button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:14px 18px;border-radius:12px;font-weight:600;font-size:13.5px;margin-bottom:22px;display:flex;align-items:flex-start;justify-content:space-between">
                    <div>
                        <strong style="display:block;margin-bottom:4px">⚠️ Please check the form errors:</strong>
                        <ul style="margin:0;padding-left:18px;font-weight:500;font-size:13px">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:800;color:#991B1B;font-size:16px">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div style="background:#FEF3C7;border:1px solid #FCD34D;color:#92400E;padding:14px 18px;border-radius:12px;font-weight:700;font-size:13.5px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between">
                    <div>⚠️ {{ session('warning') }}</div>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:800;color:#92400E">&times;</button>
                </div>
            @endif

            @yield('content')

        </main>

    </div>

    <script>
        function toggleSidebar() {
            const sb = document.getElementById('adminSidebar');
            sb.classList.toggle('show');
        }

        function toggleProfileMenu() {
            const menu = document.getElementById('profileDropdownMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(e) {
            const dd = document.querySelector('.user-dropdown');
            const menu = document.getElementById('profileDropdownMenu');
            if (dd && !dd.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        function handleGlobalSearch(e) {
            if (e.key === 'Enter') {
                const term = e.target.value.trim();
                if (term) {
                    window.location.href = "{{ url('/admin/orders') }}?search=" + encodeURIComponent(term);
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
