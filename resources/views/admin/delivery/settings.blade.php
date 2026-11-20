<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Partner Settings — Shiprocket Integration</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-app.css') }}">
    <style>
        .settings-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; padding: 22px; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .settings-card-title { font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 700; color: #374151; }
        .form-group input, .form-group select, .form-group textarea {
            padding: 9px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 13px; transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #01684B; box-shadow: 0 0 0 3px rgba(1, 104, 75, 0.12);
        }
        .form-group small { font-size: 11.5px; color: #6B7280; }
        .toggle-switch-card { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; }
        .btn-action { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; }
        .delivery-table { width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left; }
        .delivery-table th { background: #F9FAFB; padding: 10px 12px; font-weight: 700; color: #4B5563; border-bottom: 1px solid #E5E7EB; }
        .delivery-table td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
    </style>
</head>
<body style="background:#F3F4F6">

    <!-- Top Admin Header -->
    <header style="background:#01684B;color:#fff;padding:14px 0;box-shadow:0 4px 15px rgba(0,0,0,0.12);position:sticky;top:0;z-index:100">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
            <div style="display:flex;align-items:center;gap:14px">
                <a href="{{ url('/admin') }}" style="background:#fff;padding:5px 10px;border-radius:8px;display:flex;align-items:center">
                    <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}" alt="TOUCHSTAIN" style="height:34px;object-fit:contain">
                </a>
                <div>
                    <h1 style="font-size:18px;font-weight:800;margin:0">⚙️ Shiprocket Settings & Configuration</h1>
                    <p style="font-size:11.5px;opacity:0.85;margin:0">API Credentials, Warehouse Location, Automation & Webhooks</p>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ url('/admin/delivery') }}" class="btn-outline" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);padding:7px 14px;font-size:12.5px">🚚 Delivery Dashboard</a>
                <a href="{{ url('/admin') }}" class="btn-outline" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);padding:7px 14px;font-size:12.5px">← Main Admin</a>
            </div>
        </div>
    </header>

    <main class="container" style="padding:24px 16px 60px">

        @if(session('success'))
            <div style="background:#D1FAE5;border:1px solid #059669;color:#065F46;padding:14px 18px;border-radius:10px;font-weight:700;margin-bottom:20px">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#FEE2E2;border:1px solid #DC2626;color:#991B1B;padding:14px 18px;border-radius:10px;font-weight:700;margin-bottom:20px">
                ❌ {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div style="background:#FEE2E2;border:1px solid #DC2626;color:#991B1B;padding:14px 18px;border-radius:10px;font-weight:600;margin-bottom:20px">
                <ul style="margin:0;padding-left:18px">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Connection Test Ribbon -->
        <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <span id="connIndicator" style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{ $settings->connection_status === 'connected' ? '#10B981' : '#F59E0B' }}"></span>
                <div>
                    <div style="font-size:14px;font-weight:800;color:#111827">
                        Shiprocket API Status: 
                        <span id="connStatusText" style="color:{{ $settings->connection_status === 'connected' ? '#059669' : '#D97706' }}">
                            {{ strtoupper($settings->connection_status) }}
                        </span>
                        <span style="font-size:12px;font-weight:600;background:#E5E7EB;padding:2px 8px;border-radius:6px;margin-left:6px">
                            {{ strtoupper($settings->environment) }} MODE
                        </span>
                    </div>
                    <div id="connSubText" style="font-size:12px;color:#6B7280;margin-top:2px">
                        @if($settings->token_expires_at)
                            Token expires: {{ \Carbon\Carbon::parse($settings->token_expires_at)->diffForHumans() }} (Auto-refreshed upon expiration)
                        @else
                            No active token cached. Click "Test API Connection" to test credentials.
                        @endif
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <button type="button" onclick="testConnectionAjax()" id="btnTestConn" class="btn-action" style="background:#01684B;color:#fff">
                    ⚡ Test API Connection
                </button>
            </div>
        </div>

        <form action="{{ url('/admin/delivery/settings') }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:1.8fr 1.2fr;gap:24px;align-items:start">
                
                <!-- Left Column: Settings Form -->
                <div>
                    <!-- 1. API Credentials -->
                    <div class="settings-card">
                        <h2 class="settings-card-title">🔐 Shiprocket API Authentication</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="environment">API Mode / Environment *</label>
                                <select name="environment" id="environment" required>
                                    <option value="test" {{ $settings->environment === 'test' ? 'selected' : '' }}>🧪 Test / Sandbox (Demo Courier Simulation)</option>
                                    <option value="production" {{ $settings->environment === 'production' ? 'selected' : '' }}>🚀 Production (Live Shiprocket API)</option>
                                </select>
                                <small>Test mode works seamlessly even if your KYC is pending, generating simulated AWBs and test tracking events.</small>
                            </div>

                            <div class="form-group">
                                <label for="api_email">Shiprocket Account Email *</label>
                                <input type="email" name="api_email" id="api_email" value="{{ old('api_email', $settings->api_email) }}" placeholder="e.g. yourname@domain.com" required>
                                <small>Registered email used to log in at app.shiprocket.in</small>
                            </div>

                            <div class="form-group" style="grid-column:1 / -1">
                                <label for="api_password">Shiprocket Account Password</label>
                                <input type="password" name="api_password" id="api_password" placeholder="{{ $settings->api_password ? '•••••••••••• (Leave blank to keep existing password)' : 'Enter your Shiprocket password' }}">
                                <small>Stored securely in encrypted Laravel database storage. Never exposed to front-end.</small>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pickup Warehouse & Dispatch Info -->
                    <div class="settings-card">
                        <h2 class="settings-card-title">📍 Warehouse & Pickup Location</h2>
                        <div style="background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:16px">
                            ℹ️ <strong>Important:</strong> <em>Pickup Location Nickname</em> must match the name configured under Shiprocket &gt; Settings &gt; Company &gt; Pickup Address (e.g. <code>Primary</code> or <code>Warehouse1</code>).
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="pickup_location_name">Pickup Location Nickname *</label>
                                <input type="text" name="pickup_location_name" id="pickup_location_name" value="{{ old('pickup_location_name', $settings->pickup_location_name) }}" required>
                                <small>Exact name as entered in Shiprocket dashboard (e.g. "Primary")</small>
                            </div>

                            <div class="form-group">
                                <label for="warehouse_name">Warehouse / Business Name *</label>
                                <input type="text" name="warehouse_name" id="warehouse_name" value="{{ old('warehouse_name', $settings->warehouse_name) }}" required>
                            </div>

                            <div class="form-group" style="grid-column:1 / -1">
                                <label for="warehouse_address">Full Warehouse Street Address *</label>
                                <textarea name="warehouse_address" id="warehouse_address" rows="2" required>{{ old('warehouse_address', $settings->warehouse_address) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="warehouse_city">City *</label>
                                <input type="text" name="warehouse_city" id="warehouse_city" value="{{ old('warehouse_city', $settings->warehouse_city) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="warehouse_state">State *</label>
                                <input type="text" name="warehouse_state" id="warehouse_state" value="{{ old('warehouse_state', $settings->warehouse_state) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="warehouse_pincode">Warehouse Pincode (6 digits) *</label>
                                <input type="text" name="warehouse_pincode" id="warehouse_pincode" maxlength="6" value="{{ old('warehouse_pincode', $settings->warehouse_pincode) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_person">Contact Person Name *</label>
                                <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $settings->contact_person) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_number">Contact Phone Number *</label>
                                <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $settings->contact_number) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="gst_number">GST Number (Optional)</label>
                                <input type="text" name="gst_number" id="gst_number" value="{{ old('gst_number', $settings->gst_number) }}" placeholder="e.g. 27ABCDE1234F1Z5">
                            </div>
                        </div>

                        <div style="margin-top:16px;display:flex;justify-content:flex-end">
                            <button type="button" onclick="registerPickupLocationAjax()" id="btnRegisterPickup" class="btn-action" style="background:#2563EB;color:#fff;font-size:12px">
                                📍 Sync & Register Pickup Address on Shiprocket
                            </button>
                        </div>
                    </div>

                    <!-- 3. Default Package Dimensions -->
                    <div class="settings-card">
                        <h2 class="settings-card-title">📦 Default Package Dimensions & Weight</h2>
                        <p style="font-size:12px;color:#6B7280;margin:0 0 14px">These default values are used when an order does not have specific individual product weights specified.</p>

                        <div class="form-grid" style="grid-template-columns:repeat(4, 1fr)">
                            <div class="form-group">
                                <label for="default_weight">Weight (KG) *</label>
                                <input type="number" step="0.01" min="0.1" name="default_weight" id="default_weight" value="{{ old('default_weight', $settings->default_weight) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="default_length">Length (CM) *</label>
                                <input type="number" step="0.1" min="1" name="default_length" id="default_length" value="{{ old('default_length', $settings->default_length) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="default_breadth">Breadth (CM) *</label>
                                <input type="number" step="0.1" min="1" name="default_breadth" id="default_breadth" value="{{ old('default_breadth', $settings->default_breadth) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="default_height">Height (CM) *</label>
                                <input type="number" step="0.1" min="1" name="default_height" id="default_height" value="{{ old('default_height', $settings->default_height) }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Automation Settings -->
                    <div class="settings-card">
                        <h2 class="settings-card-title">⚡ Automation & Dispatch Workflow</h2>
                        
                        <div style="display:flex;flex-direction:column;gap:12px">
                            <label class="toggle-switch-card">
                                <div>
                                    <div style="font-size:13.5px;font-weight:700;color:#111827">Automatic Shipment Creation on Order Confirmation</div>
                                    <div style="font-size:12px;color:#6B7280">When order status changes to "Confirmed", immediately book shipment with Shiprocket.</div>
                                </div>
                                <input type="checkbox" name="auto_shipment_on_confirm" value="1" {{ $settings->auto_shipment_on_confirm ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#01684B">
                            </label>

                            <label class="toggle-switch-card">
                                <div>
                                    <div style="font-size:13.5px;font-weight:700;color:#111827">Automatic Courier Selection (Cheapest / Fastest)</div>
                                    <div style="font-size:12px;color:#6B7280">Automatically pick the top rated serviceable courier partner with lowest freight.</div>
                                </div>
                                <input type="checkbox" name="auto_courier_selection" value="1" {{ $settings->auto_courier_selection ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#01684B">
                            </label>

                            <label class="toggle-switch-card">
                                <div>
                                    <div style="font-size:13.5px;font-weight:700;color:#111827">Automatic AWB Generation</div>
                                    <div style="font-size:12px;color:#6B7280">Generate AWB number right after creating order in Shiprocket without manual click.</div>
                                </div>
                                <input type="checkbox" name="auto_generate_awb" value="1" {{ $settings->auto_generate_awb ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#01684B">
                            </label>

                            <label class="toggle-switch-card">
                                <div>
                                    <div style="font-size:13.5px;font-weight:700;color:#111827">Automatic Pickup Scheduling</div>
                                    <div style="font-size:12px;color:#6B7280">Schedule courier pickup for the next available pickup slot automatically.</div>
                                </div>
                                <input type="checkbox" name="auto_schedule_pickup" value="1" {{ $settings->auto_schedule_pickup ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#01684B">
                            </label>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-bottom:30px">
                        <button type="submit" class="btn-action" style="background:#01684B;color:#fff;padding:12px 28px;font-size:14px">
                            💾 Save Shiprocket Settings
                        </button>
                    </div>

                </div>

                <!-- Right Column: Webhook & Info Cards -->
                <div>
                    
                    <!-- Webhook Card -->
                    <div class="settings-card" style="border-top:4px solid #10B981">
                        <h2 class="settings-card-title">📡 Shiprocket Webhook Endpoint</h2>
                        <p style="font-size:12px;color:#6B7280;margin:0 0 12px">Configure this URL in Shiprocket to get real-time tracking updates (AWB assigned, In Transit, Out for Delivery, Delivered, NDR, RTO) instantly.</p>
                        
                        <div class="form-group" style="margin-bottom:14px">
                            <label>Webhook URL (POST):</label>
                            <div style="display:flex;gap:6px">
                                <input type="text" id="webhookUrlInput" readonly value="{{ url('/api/v1/delivery/webhook/shiprocket') }}" style="background:#F9FAFB;font-family:monospace;font-size:11.5px">
                                <button type="button" onclick="copyToClipboard('webhookUrlInput')" class="delivery-btn-sm" style="background:#E5E7EB;border:1px solid #D1D5DB;padding:0 12px">📋 Copy</button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:14px">
                            <label>Security Token:</label>
                            <div style="display:flex;gap:6px">
                                <input type="text" id="webhookTokenInput" readonly value="{{ $settings->webhook_token }}" style="background:#F9FAFB;font-family:monospace;font-size:11.5px">
                                <button type="button" onclick="copyToClipboard('webhookTokenInput')" class="delivery-btn-sm" style="background:#E5E7EB;border:1px solid #D1D5DB;padding:0 12px">📋 Copy</button>
                            </div>
                        </div>

                        <div style="background:#F9FAFB;border-radius:8px;padding:12px;font-size:11.5px;color:#4B5563">
                            <strong style="color:#111827">Setup in Shiprocket Dashboard:</strong>
                            <ol style="margin:6px 0 0;padding-left:16px;line-height:1.6">
                                <li>Log in to <strong>app.shiprocket.in</strong></li>
                                <li>Go to <strong>Settings → API → Webhooks</strong></li>
                                <li>Click <strong>Add Webhook</strong></li>
                                <li>Select events: <em>Shipment, Order, Tracking</em></li>
                                <li>Paste the Webhook URL above</li>
                                <li>Save Webhook</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Scheduled Cron Sync Card -->
                    <div class="settings-card">
                        <h2 class="settings-card-title">⏰ Fallback Tracking Sync (Cron)</h2>
                        <p style="font-size:12px;color:#6B7280;margin:0 0 12px">In case webhook events are missed, the built-in tracking synchronizer queries Shiprocket every 30 minutes for active shipments.</p>
                        
                        <div style="background:#111827;color:#F9FAFB;padding:10px 14px;border-radius:8px;font-family:monospace;font-size:11px;overflow-x:auto;margin-bottom:10px">
                            * * * * * cd /home/path/to/project && php artisan schedule:run >> /dev/null 2>&1
                        </div>
                        <div style="font-size:11.5px;color:#4B5563">
                            Or run manually via terminal: <code style="background:#E5E7EB;padding:2px 6px;border-radius:4px">php artisan shiprocket:sync-tracking</code>
                        </div>
                    </div>

                    <!-- Quick Support Guide -->
                    <div class="settings-card" style="background:#F0FDF4;border-color:#BBF7D0">
                        <h3 style="font-size:14px;font-weight:800;color:#166534;margin:0 0 8px">💡 Need Shiprocket Help?</h3>
                        <p style="font-size:12px;color:#15803D;line-height:1.5;margin:0">
                            - Register or manage orders at <a href="https://app.shiprocket.in" target="_blank" style="color:#01684B;font-weight:700">app.shiprocket.in</a><br>
                            - Support Email: support@shiprocket.in<br>
                            - Ensure your wallet balance has recharge funds in production mode for courier booking.
                        </p>
                    </div>

                </div>

            </div>

        </form>

        <!-- API Audit Logs Section -->
        <div class="settings-card" style="margin-top:10px">
            <h2 class="settings-card-title">📊 Recent Shiprocket API Audit Logs (Last 15 Requests)</h2>
            <p style="font-size:12px;color:#6B7280;margin:0 0 14px">Audit trail of outbound API calls to Shiprocket with status codes and execution times for debugging.</p>

            <div style="overflow-x:auto">
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Endpoint</th>
                            <th>Method</th>
                            <th>Status Code</th>
                            <th>Response Status</th>
                            <th>Duration</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td style="white-space:nowrap;color:#6B7280">{{ $log->created_at->format('d M H:i:s') }}</td>
                                <td><code style="background:#F3F4F6;padding:2px 6px;border-radius:4px;font-size:11.5px">{{ $log->endpoint }}</code></td>
                                <td>
                                    <span style="font-weight:700;font-size:11px;padding:2px 6px;border-radius:4px;background:{{ $log->method === 'POST' ? '#DBEAFE' : '#E0E7FF' }};color:{{ $log->method === 'POST' ? '#1E40AF' : '#3730A3' }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:800;font-size:11.5px;color:{{ $log->response_status >= 200 && $log->response_status < 300 ? '#059669' : '#DC2626' }}">
                                        {{ $log->response_status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->is_success)
                                        <span style="background:#D1FAE5;color:#065F46;font-size:11px;padding:2px 8px;border-radius:999px;font-weight:700">SUCCESS</span>
                                    @else
                                        <span style="background:#FEE2E2;color:#991B1B;font-size:11px;padding:2px 8px;border-radius:999px;font-weight:700">FAILED</span>
                                    @endif
                                </td>
                                <td style="color:#6B7280;font-size:12px">{{ $log->duration_ms ?? 0 }} ms</td>
                                <td>
                                    <button type="button" onclick="showLogDetails({{ json_encode($log) }})" class="delivery-btn-sm" style="background:#F3F4F6;color:#374151;border:1px solid #D1D5DB;font-size:11px;padding:3px 8px">
                                        View Payload
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:24px;color:#9CA3AF">No API logs recorded yet. Run a connection test or dispatch a shipment to see logs.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal for Log Payload -->
    <div id="logModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:99999;padding:20px">
        <div style="background:#fff;border-radius:12px;max-width:700px;width:100%;max-height:85vh;overflow-y:auto;padding:22px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid #E5E7EB;padding-bottom:10px">
                <h3 id="modalLogTitle" style="font-size:15px;font-weight:800;margin:0">API Log Details</h3>
                <button type="button" onclick="closeLogModal()" style="border:none;background:none;font-size:18px;cursor:pointer;color:#6B7280">&times;</button>
            </div>
            
            <div style="margin-bottom:12px">
                <label style="font-size:12px;font-weight:700;color:#374151">Request Payload:</label>
                <pre id="modalReqPayload" style="background:#111827;color:#F9FAFB;padding:12px;border-radius:8px;font-size:11px;overflow-x:auto;max-height:180px"></pre>
            </div>

            <div>
                <label style="font-size:12px;font-weight:700;color:#374151">Response Payload:</label>
                <pre id="modalResPayload" style="background:#111827;color:#10B981;padding:12px;border-radius:8px;font-size:11px;overflow-x:auto;max-height:220px"></pre>
            </div>

            <div style="margin-top:16px;text-align:right">
                <button type="button" onclick="closeLogModal()" class="btn-action" style="background:#E5E7EB;color:#374151">Close</button>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(id) {
            const input = document.getElementById(id);
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                alert('Copied to clipboard: ' + input.value);
            });
        }

        function testConnectionAjax() {
            const btn = document.getElementById('btnTestConn');
            const ind = document.getElementById('connIndicator');
            const txt = document.getElementById('connStatusText');
            const sub = document.getElementById('connSubText');

            btn.disabled = true;
            btn.innerHTML = '⏳ Testing connection...';

            fetch('{{ url("/admin/delivery/test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '⚡ Test API Connection';

                if (data.success) {
                    ind.style.background = '#10B981';
                    txt.style.color = '#059669';
                    txt.innerText = 'CONNECTED';
                    sub.innerText = (data.message || 'Connection successful!') + ' (Expires in: ' + (data.expires_in_hours || 240) + ' hrs)';
                    alert('✅ ' + data.message);
                } else {
                    ind.style.background = '#EF4444';
                    txt.style.color = '#DC2626';
                    txt.innerText = 'FAILED';
                    sub.innerText = data.message || 'API connection failed. Please check credentials.';
                    alert('❌ Connection Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '⚡ Test API Connection';
                ind.style.background = '#EF4444';
                txt.style.color = '#DC2626';
                txt.innerText = 'ERROR';
                sub.innerText = 'Network or server error while connecting.';
                alert('❌ Error: ' + err.message);
            });
        }

        function registerPickupLocationAjax() {
            const btn = document.getElementById('btnRegisterPickup');
            btn.disabled = true;
            btn.innerHTML = '⏳ Registering on Shiprocket...';

            fetch('{{ url("/admin/delivery/register-pickup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '📍 Sync & Register Pickup Address on Shiprocket';
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '📍 Sync & Register Pickup Address on Shiprocket';
                alert('❌ Error: ' + err.message);
            });
        }

        function showLogDetails(log) {
            document.getElementById('modalLogTitle').innerText = `${log.method} ${log.endpoint} (${log.response_status || 'N/A'})`;
            
            try {
                const req = typeof log.request_payload === 'string' ? JSON.parse(log.request_payload) : log.request_payload;
                document.getElementById('modalReqPayload').innerText = JSON.stringify(req, null, 2);
            } catch(e) {
                document.getElementById('modalReqPayload').innerText = log.request_payload || 'None';
            }

            try {
                const res = typeof log.response_payload === 'string' ? JSON.parse(log.response_payload) : log.response_payload;
                document.getElementById('modalResPayload').innerText = JSON.stringify(res, null, 2);
            } catch(e) {
                document.getElementById('modalResPayload').innerText = log.response_payload || 'None';
            }

            document.getElementById('logModal').style.display = 'flex';
        }

        function closeLogModal() {
            document.getElementById('logModal').style.display = 'none';
        }
    </script>
</body>
</html>
