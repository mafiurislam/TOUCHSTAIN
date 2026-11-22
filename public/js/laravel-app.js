/**
 * GETGOZO LARAVEL + SQLITE INTERACTIVE ENGINE (public/js/laravel-app.js)
 */
(function () {
  const DATA = window.GETGOZO_DATA || {};
  const ALL_PRODUCTS = DATA.allProducts || [];
  const I18N = window.GETGOZO_I18N || {};

  const GetgozoApp = {
    state: {
      cart: JSON.parse(localStorage.getItem('getgozo_cart_v2') || '{}'),
      wishlist: JSON.parse(localStorage.getItem('getgozo_wishlist_v2') || '[]'),
      lang: localStorage.getItem('getgozo_lang') || 'en',
      theme: localStorage.getItem('getgozo_theme') || 'light',
      appliedCoupon: null,
      deliveryType: 'delivery',
      selectedTimeSlot: 'Express (Within 45 - 60 Mins)',
      paymentMethod: 'razorpay',
      homeLoadedCount: 18,
      user: window.LARAVEL_SQLITE_USER || {
        f_name: 'Sabir',
        l_name: 'Hossain',
        phone: '+91 7679416294 / 7039214465',
        wallet_balance: 250,
        loyalty_point: 140
      }
    },

    findProduct(id) {
      return ALL_PRODUCTS.find(p => Number(p.id) === Number(id));
    },

    getSellingPrice(p) {
      return Math.max(0, Math.round(parseFloat(p.price || 0) * 100) / 100);
    },

    getImgUrl(p) {
      const img = Array.isArray(p.image) ? p.image[0] : p.image;
      return img ? '/storage/product/' + img : '/assets/image/placeholder.png';
    },

    addToCart(productId, delta = 1, ev) {
      if (ev) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      if (delta > 0 && window.TouchstainsAuth && !window.TouchstainsAuth.isLoggedIn()) {
        this.showToast('🔔 অর্ডার বা কার্টে অ্যাড করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
        window.TouchstainsAuth.open('register', () => {
          this.addToCart(productId, delta);
        });
        return;
      }
      const p = this.findProduct(productId);
      if (!p) return;
      const cur = this.state.cart[productId] || 0;
      const maxQ = Math.max(1, parseInt(p.total_stock || 99, 10));
      // Minimum quantity is always 1 when pressing minus; use removeFromCart (✕) to delete item
      if (delta < 0 && cur <= 1) {
        this.state.cart[productId] = 1;
        this.saveCart();
        return;
      }
      const next = Math.max(1, cur + delta);
      if (next > maxQ) {
        this.showToast('Maximum available stock is ' + maxQ, 'error');
        return;
      }
      this.state.cart[productId] = next;
      if (delta > 0) this.showToast(p.name + ' (×' + next + ') added to Bag!', 'success');
      this.saveCart();
    },

    removeFromCart(productId, ev) {
      if (ev) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      if (this.state.cart[productId] !== undefined) {
        delete this.state.cart[productId];
        this.saveCart();
        this.showToast('Removed from Shopping Bag', 'info');
      }
    },

    buyNow(productId, qty = 1, ev) {
      if (ev) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      if (window.TouchstainsAuth && !window.TouchstainsAuth.isLoggedIn()) {
        this.showToast('🔔 অর্ডার করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
        window.TouchstainsAuth.open('register', () => {
          this.buyNow(productId, qty);
        });
        return;
      }
      const p = this.findProduct(productId);
      if (!p) return;
      const maxQ = Math.max(1, parseInt(p.total_stock || 99, 10));
      const cleanQty = Math.max(1, Math.min(maxQ, parseInt(qty || 1, 10)));
      // Replace cart with exact selected product & quantity so Checkout reflects exact price × qty!
      this.state.cart = {};
      this.state.cart[productId] = cleanQty;
      this.saveCart();
      window.location.href = '/checkout';
    },

    clearCart() {
      this.state.cart = {};
      this.state.appliedCoupon = null;
      this.saveCart();
      this.showToast('Shopping bag cleared', 'info');
    },

    saveCart() {
      localStorage.setItem('getgozo_cart_v2', JSON.stringify(this.state.cart));
      this.refreshCartUI();
    },

    getCartTotals() {
      // Auto-migrate any legacy static cart items if getgozo_cart_v2 is empty
      if (Object.keys(this.state.cart || {}).length === 0) {
        try {
          const legacy = JSON.parse(localStorage.getItem('getgozo_cart') || '[]');
          if (Array.isArray(legacy) && legacy.length > 0) {
            legacy.forEach(it => {
              if (it && it.id && it.qty > 0) {
                this.state.cart[it.id] = (this.state.cart[it.id] || 0) + parseInt(it.qty, 10);
              }
            });
            localStorage.setItem('getgozo_cart_v2', JSON.stringify(this.state.cart));
          }
        } catch (e) {}
      }

      const items = [];
      let mrpTotal = 0;
      let subtotal = 0;
      let totalQty = 0;

      Object.keys(this.state.cart).forEach(pid => {
        const qty = this.state.cart[pid];
        const p = this.findProduct(pid);
        if (p && qty > 0) {
          const sp = this.getSellingPrice(p);
          const mrp = parseFloat(p.price || sp);
          mrpTotal += mrp * qty;
          subtotal += sp * qty;
          totalQty += qty;
          items.push({
            id: p.id,
            name: p.name,
            image: Array.isArray(p.image) ? p.image[0] : p.image,
            unit: (p.capacity || 1) + ' ' + (p.unit || 'pc'),
            price: sp,
            mrp: mrp,
            discount_amount: Math.max(0, mrp - sp),
            qty: qty
          });
        }
      });

      const totalDiscount = Math.max(0, mrpTotal - subtotal);
      let couponDiscount = 0;
      if (this.state.appliedCoupon && subtotal >= (this.state.appliedCoupon.min || 99)) {
        couponDiscount = this.state.appliedCoupon.type === 'percent'
          ? Math.min(this.state.appliedCoupon.max || 100, Math.round((subtotal * this.state.appliedCoupon.val) / 100))
          : this.state.appliedCoupon.val;
      }

      const freeOver = 69;
      const deliveryFee = (items.length === 0 || subtotal >= freeOver || this.state.deliveryType === 'self_pickup') ? 0 : 40;
      const grandTotal = Math.max(0, subtotal - couponDiscount + deliveryFee);

      return { items, totalQty, mrpTotal, subtotal, totalDiscount, couponDiscount, deliveryFee, freeOver, grandTotal };
    },

    refreshCartUI() {
      const totals = this.getCartTotals();

      // Update Header & Mobile Badges
      document.querySelectorAll('[data-cart-count]').forEach(el => {
        el.textContent = totals.totalQty || 0;
        el.style.display = 'flex';
      });

      // Update Product Card Steppers
      document.querySelectorAll('[data-cart-ctrl]').forEach(ctrl => {
        const pid = ctrl.getAttribute('data-cart-ctrl');
        const qty = this.state.cart[pid] || 0;
        const p = this.findProduct(pid);
        if (p && p.total_stock <= 0) return;
        if (qty > 0) {
          ctrl.innerHTML = `
            <div class="qty-stepper">
              <button type="button" onclick="GetgozoApp.addToCart(${pid}, -1, event)">−</button>
              <span>${qty}</span>
              <button type="button" onclick="GetgozoApp.addToCart(${pid}, 1, event)">+</button>
            </div>
          `;
        } else {
          ctrl.innerHTML = `
            <button type="button" class="add-btn" onclick="GetgozoApp.addToCart(${pid}, 1, event)">
              <span>+</span> <span>Add</span>
            </button>
          `;
        }
      });

      // Update Slide-out Cart Drawer
      const drawerBody = document.getElementById('cart-drawer-body');
      const freeBar = document.getElementById('drawer-free-delivery-msg');
      if (freeBar) {
        const pct = Math.min(100, Math.round((totals.subtotal / 69) * 100));
        freeBar.innerHTML = totals.subtotal >= 69
          ? `🎉 You unlocked <strong>FREE Doorstep Delivery</strong>! <div class="free-delivery-progress"><div class="free-delivery-fill" style="width:100%"></div></div>`
          : `Add <strong>₹${Math.max(1, Math.ceil(69 - totals.subtotal))}</strong> more for <strong>FREE Delivery</strong>! <div class="free-delivery-progress"><div class="free-delivery-fill" style="width:${pct}%"></div></div>`;
      }

      if (drawerBody) {
        if (totals.items.length === 0) {
          drawerBody.innerHTML = `
            <div style="text-align:center;padding:48px 16px">
              <img src="/assets/image/empty_cart.png" alt="Empty Bag" style="width:110px;margin:0 auto 14px;opacity:0.8">
              <h4 style="font-size:16px;font-weight:700;margin-bottom:6px">Your Shopping Bag is Empty</h4>
              <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px">Add fresh vegetables, fruits & groceries to get started!</p>
              <a href="/shop" class="btn-primary" onclick="GetgozoApp.closeCartDrawer()">Browse Groceries</a>
            </div>
          `;
        } else {
          drawerBody.innerHTML = totals.items.map(item => `
            <div style="display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid var(--border)">
              <img src="/storage/product/${item.image}" alt="${item.name}" style="width:50px;height:50px;object-fit:contain;background:#f9fafb;border-radius:8px;padding:4px;border:1px solid var(--border);flex-shrink:0" onerror="this.src='/assets/image/placeholder.png'">
              <div style="flex:1;min-width:0">
                <a href="/product/${item.id}" style="font-size:13px;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</a>
                <span style="font-size:11px;color:var(--text-muted)">${item.unit}</span>
                <div style="font-size:13.5px;font-weight:700;color:var(--primary)">₹${Math.round(item.price * item.qty * 100) / 100}</div>
              </div>
              <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
                <div class="qty-stepper">
                  <button type="button" onclick="GetgozoApp.addToCart(${item.id}, -1, event)" ${item.qty <= 1 ? 'disabled style="opacity:0.45;cursor:not-allowed"' : ''}>−</button>
                  <span>${item.qty}</span>
                  <button type="button" onclick="GetgozoApp.addToCart(${item.id}, 1, event)">+</button>
                </div>
                <button type="button" onclick="GetgozoApp.removeFromCart(${item.id}, event)" title="Remove item" aria-label="Remove item" style="width:28px;height:28px;border-radius:50%;border:1px solid #FECACA;background:#FEF2F2;color:#DC2626;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0">✕</button>
              </div>
            </div>
          `).join('');
        }
        const subEl = document.getElementById('drawer-subtotal');
        const discEl = document.getElementById('drawer-discount');
        const totEl = document.getElementById('drawer-total');
        if (subEl) subEl.textContent = '₹' + totals.mrpTotal.toFixed(0);
        if (discEl) discEl.textContent = '- ₹' + totals.totalDiscount.toFixed(0);
        if (totEl) totEl.textContent = '₹' + totals.grandTotal.toFixed(0);
      }

      // Update Cart Page if active
      const cartPageList = document.getElementById('cart-page-items-list');
      if (cartPageList) {
        if (totals.items.length === 0) {
          cartPageList.innerHTML = `
            <div style="text-align:center;padding:48px 16px">
              <img src="/assets/image/empty_cart.png" alt="Empty" style="width:120px;margin:0 auto 16px">
              <h3>Your Shopping Bag is Empty</h3>
              <p style="color:var(--text-secondary);margin:8px 0 18px">Explore our 7 categories and 240+ products in Jadupur!</p>
              <a href="/shop" class="btn-primary">Start Shopping →</a>
            </div>
          `;
        } else {
          cartPageList.innerHTML = totals.items.map(item => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border);gap:14px;flex-wrap:wrap">
              <div style="display:flex;align-items:center;gap:14px">
                <img src="/storage/product/${item.image}" alt="${item.name}" style="width:64px;height:64px;object-fit:contain;background:#f9fafb;border-radius:10px;padding:6px;border:1px solid var(--border)" onerror="this.src='/assets/image/placeholder.png'">
                <div>
                  <a href="/product/${item.id}" style="font-size:15px;font-weight:700">${item.name}</a>
                  <div style="font-size:12px;color:var(--text-muted)">Unit: ${item.unit} • Price: ₹${item.price}</div>
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:12px">
                <div class="qty-stepper">
                  <button type="button" onclick="GetgozoApp.addToCart(${item.id}, -1, event)" ${item.qty <= 1 ? 'disabled style="opacity:0.45;cursor:not-allowed"' : ''}>−</button>
                  <span>${item.qty}</span>
                  <button type="button" onclick="GetgozoApp.addToCart(${item.id}, 1, event)">+</button>
                </div>
                <strong style="font-size:16px;color:var(--primary);min-width:65px;text-align:right">₹${Math.round(item.price * item.qty * 100) / 100}</strong>
                <button type="button" onclick="GetgozoApp.removeFromCart(${item.id}, event)" title="Remove item" aria-label="Remove item" style="width:32px;height:32px;border-radius:50%;border:1px solid #FECACA;background:#FEF2F2;color:#DC2626;font-size:14px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0">✕</button>
              </div>
            </div>
          `).join('');
        }
        const setTxt = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
        setTxt('cart-summary-mrp', '₹' + totals.mrpTotal.toFixed(0));
        setTxt('cart-summary-discount', '- ₹' + totals.totalDiscount.toFixed(0));
        setTxt('cart-summary-delivery', totals.deliveryFee === 0 ? 'FREE' : '₹' + totals.deliveryFee);
        setTxt('cart-summary-total', '₹' + totals.grandTotal.toFixed(0));
        const cpRow = document.getElementById('cart-coupon-row');
        if (cpRow) {
          cpRow.style.display = totals.couponDiscount > 0 ? 'flex' : 'none';
          setTxt('cart-summary-coupon', '- ₹' + totals.couponDiscount);
        }
      }

      // Update Checkout Page if active (with live +/- quantity controls & exact price × qty)
      const chkPreview = document.getElementById('checkout-items-preview');
      if (chkPreview) {
        const fmt = (n) => {
          const val = Math.round((Number(n) || 0) * 100) / 100;
          return val % 1 === 0 ? val.toFixed(0) : val.toFixed(2);
        };
        if (totals.items.length === 0) {
          chkPreview.innerHTML = `
            <div style="text-align:center;padding:18px 8px;color:var(--text-muted);font-size:13px">
              Your shopping bag is empty. <a href="/shop" style="color:#01684B;font-weight:700;text-decoration:underline">Browse Products</a>
            </div>
          `;
        } else {
          chkPreview.innerHTML = totals.items.map(i => {
            const lineTotal = Math.round(i.price * i.qty * 100) / 100;
            return `
              <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;padding:8px 0;border-bottom:1px dashed var(--border)">
                <div style="flex:1;min-width:0">
                  <div style="font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${i.name}</div>
                  <div style="font-size:11.5px;color:var(--text-muted)">₹${fmt(i.price)} × ${i.qty}</div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
                  <div class="qty-stepper" style="height:30px;padding:0 4px;flex-shrink:0">
                    <button type="button" onclick="GetgozoApp.addToCart(${i.id}, -1, event)" ${i.qty <= 1 ? 'disabled' : ''} style="width:24px;height:28px;font-size:14px;${i.qty <= 1 ? 'opacity:0.45;cursor:not-allowed' : ''}">−</button>
                    <span style="min-width:22px;font-size:12.5px">${i.qty}</span>
                    <button type="button" onclick="GetgozoApp.addToCart(${i.id}, 1, event)" style="width:24px;height:28px;font-size:14px">+</button>
                  </div>
                  <button type="button" onclick="GetgozoApp.removeFromCart(${i.id}, event)" title="Remove item" aria-label="Remove item" style="width:26px;height:26px;border-radius:50%;border:1px solid #FECACA;background:#FEF2F2;color:#DC2626;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0">✕</button>
                </div>
                <span style="font-weight:800;color:#01684B;min-width:60px;text-align:right">₹${fmt(lineTotal)}</span>
              </div>
            `;
          }).join('');
        }
        const setTxt = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
        setTxt('chk-subtotal', '₹' + fmt(totals.subtotal));
        setTxt('chk-discount', '- ₹' + fmt(totals.totalDiscount));
        setTxt('chk-delivery', totals.deliveryFee === 0 ? 'FREE' : '₹' + fmt(totals.deliveryFee));
        setTxt('chk-total', '₹' + fmt(totals.grandTotal));
      }
    },

    async applyCouponCode(code) {
      const clean = (code || '').trim().toUpperCase();
      if (!clean) {
        this.showToast('Please enter a coupon code.', 'error');
        return;
      }
      const totals = this.getCartTotals();
      try {
        const res = await fetch('/coupon/apply', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.LARAVEL_CSRF_TOKEN || ''
          },
          body: JSON.stringify({ code: clean, subtotal: totals.subtotal })
        });
        const data = await res.json();
        if (res.ok && data && data.status) {
          this.state.appliedCoupon = {
            code: data.code,
            type: data.discount_type,
            val: data.discount_value,
            min: data.min_purchase || 0,
            max: data.max_discount || 99999
          };
          this.showToast(data.message || `Coupon ${data.code} applied!`, 'success');
          this.refreshCartUI();
          return;
        }
        this.showToast((data && data.message) || 'Invalid or expired coupon code.', 'error');
      } catch (e) {
        this.showToast('Unable to validate coupon right now.', 'error');
      }
    },

    setDeliveryType(type) {
      this.state.deliveryType = type;
      document.getElementById('opt-delivery')?.classList.toggle('selected', type === 'delivery');
      document.getElementById('opt-pickup')?.classList.toggle('selected', type === 'self_pickup');
      const addrSec = document.getElementById('checkout-address-section');
      if (addrSec) addrSec.style.display = type === 'self_pickup' ? 'none' : 'block';
      this.refreshCartUI();
    },

    selectTimeSlot(el, slot) {
      this.state.selectedTimeSlot = slot;
      document.querySelectorAll('#checkout-timeslots .option-card').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');
    },

    selectPaymentMethod(method) {
      this.state.paymentMethod = method;
      document.getElementById('pay-cod')?.classList.toggle('selected', method === 'cash_on_delivery');
      document.getElementById('pay-razorpay')?.classList.toggle('selected', method === 'razorpay');
    },

    async toggleWishlist(productId, ev) {
      if (ev) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      if (window.TouchstainsAuth && !window.TouchstainsAuth.isLoggedIn()) {
        this.showToast('🔔 ফেভারিট লিস্টে সেভ করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
        window.TouchstainsAuth.open('register', () => {
          this.toggleWishlist(productId);
        });
        return;
      }
      const pid = Number(productId);
      const idx = this.state.wishlist.indexOf(pid);
      if (idx === -1) {
        this.state.wishlist.push(pid);
        this.showToast('Added to Favourite List ♥', 'success');
      } else {
        this.state.wishlist.splice(idx, 1);
        this.showToast('Removed from Favourite List', 'info');
      }
      localStorage.setItem('getgozo_wishlist_v2', JSON.stringify(this.state.wishlist));
      this.refreshWishlistUI();

      try {
        await fetch('/wishlist/toggle', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.LARAVEL_CSRF_TOKEN || ''
          },
          body: JSON.stringify({ product_id: pid })
        });
      } catch (e) {}
    },

    refreshWishlistUI() {
      document.querySelectorAll('[data-wishlist-count]').forEach(el => {
        el.textContent = this.state.wishlist.length || 0;
        el.style.display = 'flex';
      });
      document.querySelectorAll('[data-wish-btn]').forEach(btn => {
        const pid = Number(btn.getAttribute('data-wish-btn'));
        const active = this.state.wishlist.includes(pid);
        btn.classList.toggle('active', active);
        btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="${active ? '#01684B' : 'none'}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>`;
      });
    },

    renderProductCardHTML(p) {
      const sp = this.getSellingPrice(p);
      const origPrice = parseFloat(p.mrp || 0);
      const cap = p.capacity || 1;
      const unitText = `${cap} ${p.unit || 'pc'}`;
      const inWish = this.state.wishlist.includes(Number(p.id));
      const ratingVal = (Array.isArray(p.rating) && p.rating.length > 0 && p.rating[0].average)
        ? parseFloat(p.rating[0].average).toFixed(1)
        : (p.name && p.name.toLowerCase().includes('nutrichoice') ? '5.0' : '0.0');

      return `
        <div class="gf-vcard product-card" data-product-id="${p.id}">
          <div class="gf-vcard-imgbox" style="position:relative">
            ${p.coupon_code ? `
              <div class="gf-card-coupon-badge" style="position:absolute;top:8px;left:8px;z-index:3;background:linear-gradient(135deg,#DC2626,#EF4444);color:#ffffff;padding:3px 8px;border-radius:6px;font-size:10.5px;font-weight:800;letter-spacing:0.3px;box-shadow:0 2px 6px rgba(220,38,38,0.4);display:inline-flex;align-items:center;gap:3px;pointer-events:none">
                <span>🎟️ ${p.coupon_code}</span>
              </div>
            ` : ''}
            <div class="gf-vcard-actions">
              <button type="button" class="gf-action-sq ${inWish ? 'active' : ''}" data-wish-btn="${p.id}" onclick="GetgozoApp.toggleWishlist(${p.id}, event)" title="Add to Favourites">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="${inWish ? '#01684B' : 'none'}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
              </button>
              <button type="button" class="gf-action-sq" onclick="GetgozoApp.addToCart(${p.id}, 1, event)" title="Add to Bag">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="9" cy="21" r="1"></circle>
                  <circle cx="20" cy="21" r="1"></circle>
                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
              </button>
            </div>
            <a href="/product/${p.id}" class="gf-vcard-imglink">
              <img src="${this.getImgUrl(p)}" alt="${p.name}" loading="lazy" onerror="this.src='/assets/image/placeholder.png'">
            </a>
          </div>
          <div class="gf-vcard-body">
            <div class="gf-rating-line">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="1">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
              </svg>
              <span>${ratingVal}</span>
            </div>
            <a href="/product/${p.id}" class="gf-vcard-title">${p.name}</a>
            ${p.coupon_code ? `
              <div style="background:#FEF3C7;border:1px dashed #F59E0B;color:#92400E;padding:3px 7px;border-radius:6px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:space-between;margin:4px 0 6px">
                <span>🏷️ Code: <strong>${p.coupon_code}</strong></span>
                <span style="font-size:10px;color:#B45309;font-weight:800;background:#FDE68A;padding:1px 5px;border-radius:4px">OFFER</span>
              </div>
            ` : ''}
            <div class="gf-vcard-footer">
              <span class="gf-unit-text">${unitText}</span>
              <div class="gf-price-wrap" style="display:flex;align-items:baseline;gap:6px;flex-wrap:wrap">
                ${origPrice > sp ? `<span class="gf-orig-price" style="color:#DC2626;text-decoration:line-through;font-size:12px;font-weight:600">₹ ${origPrice.toFixed(2)}</span>` : ''}
                <span class="gf-cur-price">₹ ${sp.toFixed(2)}</span>
              </div>
            </div>
          </div>
        </div>
      `;
    },

    loadMoreHomeProducts() {
      const grid = document.getElementById('home-all-products-grid');
      if (!grid) return;
      const nextSlice = ALL_PRODUCTS.slice(this.state.homeLoadedCount, this.state.homeLoadedCount + 24);
      this.state.homeLoadedCount += nextSlice.length;
      grid.insertAdjacentHTML('beforeend', nextSlice.map(p => this.renderProductCardHTML(p)).join(''));
      const btnWrap = document.getElementById('home-load-more-wrap');
      if (btnWrap) {
        const rem = Math.max(0, ALL_PRODUCTS.length - this.state.homeLoadedCount);
        if (rem === 0) btnWrap.style.display = 'none';
        else btnWrap.querySelector('button').textContent = `Load More Products (${rem} remaining) ↓`;
      }
    },

    applyShopFilters() {
      const grid = document.getElementById('shop-products-grid');
      if (!grid) return;
      const q = (document.getElementById('shop-search-input')?.value || '').trim().toLowerCase();
      const maxP = parseFloat(document.getElementById('shop-price-range')?.value || 1500);
      const inStock = document.getElementById('filter-in-stock')?.checked;
      const discounted = document.getElementById('filter-discounted')?.checked;
      const selectedCat = document.querySelector('input[name="shop_cat"]:checked')?.value || 'all';
      const sort = document.getElementById('shop-sort-select')?.value || 'default';

      const priceValEl = document.getElementById('shop-price-val');
      if (priceValEl) priceValEl.textContent = '₹' + maxP;

      let list = ALL_PRODUCTS.filter(p => {
        const sp = this.getSellingPrice(p);
        if (sp > maxP) return false;
        if (inStock && Number(p.total_stock) <= 0) return false;
        if (discounted && Number(p.discount) <= 0) return false;
        if (selectedCat !== 'all') {
          const cIds = Array.isArray(p.category_ids) ? p.category_ids : [];
          if (!cIds.some(c => String(c.id) === String(selectedCat))) return false;
        }
        if (q && !p.name.toLowerCase().includes(q)) return false;
        return true;
      });

      if (sort === 'price_asc') list.sort((a, b) => this.getSellingPrice(a) - this.getSellingPrice(b));
      else if (sort === 'price_desc') list.sort((a, b) => this.getSellingPrice(b) - this.getSellingPrice(a));
      else if (sort === 'popularity') list.sort((a, b) => (b.popularity_count || 0) - (a.popularity_count || 0));
      else if (sort === 'discount') list.sort((a, b) => (b.discount || 0) - (a.discount || 0));
      else if (sort === 'name_asc') list.sort((a, b) => a.name.localeCompare(b.name));
      else list.sort((a, b) => (Number(b.id) || 0) - (Number(a.id) || 0));

      const countEl = document.getElementById('shop-result-count');
      if (countEl) countEl.textContent = `Showing ${list.length} products`;
      grid.innerHTML = list.map(p => this.renderProductCardHTML(p)).join('');
    },

    setShopView(mode) {
      const grid = document.getElementById('shop-products-grid');
      if (!grid) return;
      grid.style.gridTemplateColumns = mode === 'list' ? 'repeat(auto-fill, minmax(380px, 1fr))' : '';
      document.getElementById('view-grid-btn')?.classList.toggle('active', mode === 'grid');
      document.getElementById('view-list-btn')?.classList.toggle('active', mode === 'list');
    },

    openCartDrawer() {
      this.refreshCartUI();
      document.getElementById('cart-slide-drawer')?.classList.add('open');
      document.getElementById('cart-drawer-backdrop')?.classList.add('open');
    },

    closeCartDrawer() {
      document.getElementById('cart-slide-drawer')?.classList.remove('open');
      document.getElementById('cart-drawer-backdrop')?.classList.remove('open');
    },

    openSideMenu() {
      document.getElementById('side-menu-drawer')?.classList.add('open');
      document.getElementById('menu-drawer-backdrop')?.classList.add('open');
    },

    closeSideMenu() {
      document.getElementById('side-menu-drawer')?.classList.remove('open');
      document.getElementById('menu-drawer-backdrop')?.classList.remove('open');
    },

    toggleTheme() {
      const isDark = document.body.classList.toggle('theme-dark');
      localStorage.setItem('getgozo_theme', isDark ? 'dark' : 'light');
      document.querySelectorAll('.theme-icon-label').forEach(el => {
        el.textContent = isDark ? '☀️ Light' : '🌙 Dark';
      });
    },

    setLang(lang) {
      this.state.lang = lang;
      localStorage.setItem('getgozo_lang', lang);
      document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
      const codeEl = document.getElementById('gf-current-lang-code');
      if (codeEl) codeEl.textContent = lang.toUpperCase();
      const dict = I18N[lang] || I18N.en || {};
      document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (dict[key]) {
          if (el.tagName === 'INPUT') el.placeholder = dict[key];
          else el.textContent = dict[key];
        }
      });
      this.showToast('Language updated to ' + lang.toUpperCase(), 'success');
    },

    startVoiceSearch() {
      this.showToast('🎙️ Listening... Try saying "Alu", "Chal", or "Mustard Oil"', 'info');
      const input = document.getElementById('header-search-input');
      if (input) input.focus();
    },

    showToast(msg, type = 'success') {
      let wrap = document.getElementById('gozo-toast-container');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'gozo-toast-container';
        wrap.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none';
        document.body.appendChild(wrap);
      }
      const t = document.createElement('div');
      t.style.cssText = `background:${type === 'error' ? '#DC2626' : '#01684B'};color:#fff;padding:10px 20px;border-radius:99px;font-size:13px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:8px`;
      t.innerHTML = `<span>${type === 'error' ? '⚠️' : '✓'}</span><span>${msg}</span>`;
      wrap.appendChild(t);
      setTimeout(() => t.remove(), 2600);
    },

    initHeroSlider() {
      const wrap = document.getElementById('heroBannerWrap') || document.querySelector('.gf-banner-carousel-wrap');
      const track = document.getElementById('heroBannerTrack') || (wrap ? wrap.querySelector('.gf-banner-track, .gf-banner-row') : null);
      const dotsContainer = document.getElementById('heroBannerDots') || document.querySelector('.gf-banner-dots');
      if (!wrap || !track) return;

      const originalSlides = Array.from(track.querySelectorAll('.gf-banner-slide:not(.clone)'));
      const N = originalSlides.length;
      if (N <= 1) return;

      // Clear existing clones if any
      track.querySelectorAll('.gf-banner-slide.clone').forEach(el => el.remove());

      // Create clones: 1 set before and 1 set after for seamless infinite sliding
      for (let i = originalSlides.length - 1; i >= 0; i--) {
        const clone = originalSlides[i].cloneNode(true);
        clone.classList.add('clone', 'clone-before');
        track.insertBefore(clone, track.firstChild);
      }
      originalSlides.forEach(slide => {
        const clone = slide.cloneNode(true);
        clone.classList.add('clone', 'clone-after');
        track.appendChild(clone);
      });

      let currentIndex = N;
      let isAnimating = false;
      let autoPlayTimer = null;

      function isMobile() {
        return window.innerWidth <= 768;
      }

      function getStep() {
        const w = wrap.offsetWidth;
        if (isMobile()) {
          return w; // Mobile: 1 card per view
        } else {
          // Desktop: 3 cards per view with 20px gap
          const gap = 20;
          const slideW = (w - (gap * 2)) / 3;
          return slideW + gap;
        }
      }

      function updateTransform(animate) {
        const step = getStep();
        const x = -(currentIndex * step);
        if (animate) {
          track.style.transition = 'transform 0.55s cubic-bezier(0.25, 1, 0.5, 1)';
          isAnimating = true;
        } else {
          track.style.transition = 'none';
          isAnimating = false;
        }
        track.style.transform = `translateX(${x}px)`;
        updateDots();
      }

      function updateDots() {
        if (!dotsContainer) return;
        const realIndex = ((currentIndex % N) + N) % N;
        const dots = dotsContainer.querySelectorAll('.gf-banner-dot');
        dots.forEach((dot, idx) => {
          dot.classList.toggle('active', idx === realIndex);
        });
      }

      function next() {
        if (isAnimating) return;
        currentIndex++;
        updateTransform(true);
      }

      function prev() {
        if (isAnimating) return;
        currentIndex--;
        updateTransform(true);
      }

      function goTo(targetIdx) {
        if (isAnimating) return;
        currentIndex = N + targetIdx;
        updateTransform(true);
        restartAutoplay();
      }

      track.addEventListener('transitionend', (e) => {
        if (e.target !== track) return;
        isAnimating = false;
        if (currentIndex >= 2 * N) {
          currentIndex = currentIndex - N;
          updateTransform(false);
        } else if (currentIndex < N) {
          currentIndex = currentIndex + N;
          updateTransform(false);
        }
      });

      function startAutoplay() {
        stopAutoplay();
        autoPlayTimer = setInterval(next, 3500);
      }

      function stopAutoplay() {
        if (autoPlayTimer) {
          clearInterval(autoPlayTimer);
          autoPlayTimer = null;
        }
      }

      function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
      }

      wrap.addEventListener('mouseenter', stopAutoplay);
      wrap.addEventListener('mouseleave', startAutoplay);

      if (dotsContainer) {
        dotsContainer.addEventListener('click', (e) => {
          const dot = e.target.closest('.gf-banner-dot');
          if (dot && dot.dataset.slide !== undefined) {
            goTo(parseInt(dot.dataset.slide, 10));
          }
        });
      }

      // Touch & Mouse Drag
      let startX = 0;
      let currentX = 0;
      let isDragging = false;
      let startOffset = 0;

      function onPointerDown(e) {
        stopAutoplay();
        isDragging = true;
        startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        currentX = startX;
        track.style.transition = 'none';
        const step = getStep();
        startOffset = -(currentIndex * step);
      }

      function onPointerMove(e) {
        if (!isDragging) return;
        currentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        const diff = currentX - startX;
        track.style.transform = `translateX(${startOffset + diff}px)`;
      }

      function onPointerUp() {
        if (!isDragging) return;
        isDragging = false;
        const diff = currentX - startX;
        const threshold = 45;
        if (diff < -threshold) {
          next();
        } else if (diff > threshold) {
          prev();
        } else {
          updateTransform(true);
        }
        startAutoplay();
      }

      wrap.addEventListener('touchstart', onPointerDown, { passive: true });
      wrap.addEventListener('touchmove', onPointerMove, { passive: true });
      wrap.addEventListener('touchend', onPointerUp);

      wrap.addEventListener('mousedown', onPointerDown);
      window.addEventListener('mousemove', onPointerMove);
      window.addEventListener('mouseup', () => {
        if (isDragging) onPointerUp();
      });

      let resizeTimer;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          updateTransform(false);
        }, 100);
      });

      // Export for external callers
      window.initHeroBannerCarousel = () => {
        updateTransform(false);
        restartAutoplay();
      };

      updateTransform(false);
      startAutoplay();
    },

    initSearchAutocomplete() {
      const input = document.getElementById('header-search-input');
      const box = document.getElementById('header-search-suggestions');
      if (!input || !box) return;
      input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        if (q.length < 1) {
          box.classList.remove('active');
          return;
        }
        const matches = ALL_PRODUCTS.filter(p => p.name.toLowerCase().includes(q)).slice(0, 7);
        if (matches.length === 0) {
          box.innerHTML = `<div style="padding:14px;text-align:center;color:var(--text-muted);font-size:13px">No items matching "${input.value}"</div>`;
        } else {
          box.innerHTML = matches.map(p => `
            <a href="/product/${p.id}" class="suggestion-item">
              <img src="${this.getImgUrl(p)}" alt="${p.name}" onerror="this.src='/assets/image/placeholder.png'">
              <div class="suggestion-info">
                <h5>${p.name}</h5>
                <span>${p.capacity || 1} ${p.unit || 'pc'}</span>
              </div>
              <div class="suggestion-price">₹${this.getSellingPrice(p)}</div>
            </a>
          `).join('');
        }
        box.classList.add('active');
      });
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-search-wrap')) box.classList.remove('active');
      });
    },

    init() {
      if (this.state.theme === 'dark') {
        document.body.classList.add('theme-dark');
      }
      this.refreshCartUI();
      this.refreshWishlistUI();
      this.initHeroSlider();
      this.initSearchAutocomplete();

      // Render Wishlist page if empty from server but has local wishlist items
      const wishGrid = document.getElementById('wishlist-page-grid');
      if (wishGrid && wishGrid.children.length === 0) {
        const items = this.state.wishlist.map(id => this.findProduct(id)).filter(Boolean);
        if (items.length > 0) {
          wishGrid.innerHTML = items.map(p => this.renderProductCardHTML(p)).join('');
        } else {
          wishGrid.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:48px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md)">
              <img src="/assets/image/empty_box.png" alt="Empty" style="width:100px;margin:0 auto 12px">
              <h3>Your Favourite List is Empty</h3>
              <p style="color:var(--text-secondary);margin:6px 0 16px">Tap the ♥ heart icon on any grocery item to save it here!</p>
              <a href="/shop" class="btn-primary">Explore Products</a>
            </div>
          `;
        }
      }
    }
  };

  window.GetgozoApp = GetgozoApp;
  document.addEventListener('DOMContentLoaded', () => GetgozoApp.init());
})();

