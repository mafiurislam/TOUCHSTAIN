// ============================================================================
// GETGOZO - Complete Core Application Engine (js/main.js)
// Synchronized with getgozo.in live API data & offline storage assets
// ============================================================================

(function () {
  const DATA = window.GETGOZO_DATA || {};
  const CONFIG = DATA.config || {};
  const ALL_PRODUCTS = DATA.allProducts || [];
  const CATEGORIES = DATA.categories || [];
  const CAT_DETAILS = DATA.catDetails || {};
  const BANNERS = DATA.banners || [];

  // Detect whether current page is in /pages/ directory or root
  const isSubPage = window.location.pathname.replace(/\\/g, '/').includes('/pages/');
  const ROOT_PREFIX = isSubPage ? '../' : '';
  const PAGES_PREFIX = isSubPage ? '' : 'pages/';

  // Persistent State
  let cart = JSON.parse(localStorage.getItem('getgozo_cart') || '[]');
  let wishlist = JSON.parse(localStorage.getItem('getgozo_wishlist') || '[]');
  let user = JSON.parse(localStorage.getItem('getgozo_user') || 'null');
  let savedAddresses = JSON.parse(localStorage.getItem('getgozo_addresses') || '[]');
  let orders = JSON.parse(localStorage.getItem('getgozo_orders') || JSON.stringify([
    {
      id: 100248,
      date: '2026-11-04 18:30',
      status: 'delivered',
      paymentMethod: 'Cash on Delivery',
      deliveryType: 'Home Delivery',
      address: 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022',
      timeSlot: 'Today (Within 45-60 mins)',
      subtotal: 190,
      discount: 29,
      deliveryCharge: 0,
      total: 161,
      items: [
        { id: 1, name: 'TOUCHSTAINS Fabric Stain Remover - Twin Bottle Kit', price: 199, oldPrice: 299, qty: 1, unit: '1 Kit (2 x 50ml)', image: 'touchstains-box-bottles-demo.jpg' },
        { id: 2, name: 'TOUCHSTAINS Fabric Rust Stain Remover', price: 149, oldPrice: 220, qty: 1, unit: '100 ml', image: 'touchstains-rust-remover-dropper.jpg' }
      ]
    }
  ]));
  let loyaltyPoints = parseInt(localStorage.getItem('getgozo_loyalty_points') || '35', 10);
  let walletBalance = parseFloat(localStorage.getItem('getgozo_wallet_balance') || '120');
  let currentLang = localStorage.getItem('getgozo_lang') || 'en';
  let currentLocation = localStorage.getItem('getgozo_location') || 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022';

  // Translation Helper
  function t(key, fallback) {
    const dict = (window.GETGOZO_I18N && window.GETGOZO_I18N[currentLang]) || (window.GETGOZO_I18N && window.GETGOZO_I18N.en) || {};
    return dict[key] || fallback || key;
  }

  // Image Path Resolver (uses local downloaded storage with fallback to live getgozo.in server)
  function getProductImageUrl(imgName) {
    if (!imgName) return ROOT_PREFIX + 'assets/image/place_holder.png';
    const file = Array.isArray(imgName) ? imgName[0] : imgName;
    if (!file) return ROOT_PREFIX + 'assets/image/place_holder.png';
    if (file.startsWith('http')) return file;
    return ROOT_PREFIX + 'storage/product/' + file;
  }

  function getCategoryImageUrl(imgName) {
    if (!imgName) return ROOT_PREFIX + 'assets/image/place_holder.png';
    return ROOT_PREFIX + 'storage/category/' + imgName;
  }

  function getBannerImageUrl(imgName) {
    if (!imgName) return ROOT_PREFIX + 'assets/image/place_holder.png';
    return ROOT_PREFIX + 'storage/banner/' + imgName;
  }

  function getLogoUrl() {
    if (CONFIG.ecommerce_logo) {
      return ROOT_PREFIX + 'storage/restaurant/' + CONFIG.ecommerce_logo + '?v=2.3';
    }
    return ROOT_PREFIX + 'storage/restaurant/touchstains-logo.png?v=2.3';
  }

  // Product Price Calculation (Direct Price, No Discount)
  function getProductPricing(product, variationPrice) {
    const rawPrice = typeof variationPrice === 'number' ? variationPrice : Number(product.price || 0);
    const cleanPrice = Math.max(0, Math.round(rawPrice * 100) / 100);
    return {
      rawPrice: cleanPrice,
      finalPrice: cleanPrice,
      hasDiscount: false,
      discountLabel: ''
    };
  }

  function getProductUnitLabel(product) {
    if (product.capacity && product.unit) {
      return `${product.capacity} ${product.unit}`;
    }
    if (product.variations && product.variations.length > 0 && product.variations[0].type) {
      return product.variations[0].type;
    }
    return product.unit || '1 pc';
  }

  // Render Exact GroFresh Vertical Product Card HTML (.gf-vcard)
  function renderProductCard(product) {
    if (!product) return '';
    const { rawPrice, finalPrice, hasDiscount } = getProductPricing(product);
    const savedAmount = Math.max(0, rawPrice - finalPrice);
    const discountBadge = `-₹ ${savedAmount.toFixed(2)}`;
    const unitLabel = getProductUnitLabel(product);
    const imgUrl = getProductImageUrl(product.image);
    const firstImg = Array.isArray(product.image) ? product.image[0] : product.image;
    const liveFallback = (firstImg && String(firstImg).startsWith('touchstains')) ? imgUrl : `https://getgozo.in/app/storage/app/public/product/${firstImg}`;
    const isWished = wishlist.includes(Number(product.id));
    const detailUrl = `${PAGES_PREFIX}product.html?id=${product.id}`;
    const ratingVal = (Array.isArray(product.rating) && product.rating.length > 0 && product.rating[0].average)
      ? parseFloat(product.rating[0].average).toFixed(1)
      : (product.name && product.name.toLowerCase().includes('nutrichoice') ? '5.0' : '0.0');

    return `
      <div class="gf-vcard product-card" data-product-id="${product.id}">
        <div class="gf-vcard-imgbox">
          ${hasDiscount && savedAmount > 0 ? `<span class="gf-discount-pill">${discountBadge}</span>` : ''}
          <div class="gf-vcard-actions">
            <button type="button" class="gf-action-sq ${isWished ? 'active' : ''}" data-wish-btn="${product.id}" onclick="window.Getgozo.toggleWishlist(${product.id}, event)" title="Favourite">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="${isWished ? '#01684B' : 'none'}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </button>
            <button type="button" class="gf-action-sq" onclick="window.Getgozo.addToCart(${product.id}, 1, null, event)" title="Add to Bag">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
            </button>
          </div>
          <a href="${detailUrl}" class="gf-vcard-imglink">
            <img src="${imgUrl}" alt="${escapeHtml(product.name)}" loading="lazy" onerror="if(this.src!=='${liveFallback}'){this.src='${liveFallback}';}else{this.src='${ROOT_PREFIX}assets/image/place_holder.png';}">
          </a>
        </div>
        <div class="gf-vcard-body">
          <div class="gf-rating-line">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="1">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>${ratingVal}</span>
          </div>
          <a href="${detailUrl}" class="gf-vcard-title" title="${escapeHtml(product.name)}">${escapeHtml(product.name)}</a>
          <div class="gf-vcard-footer">
            <span class="gf-unit-text">${escapeHtml(unitLabel)}</span>
            <div class="gf-price-wrap" style="display:flex;align-items:baseline;gap:6px;flex-wrap:wrap">
              ${Number(product.mrp || 0) > finalPrice ? `<span class="gf-orig-price" style="color:#DC2626;text-decoration:line-through;font-size:12px;font-weight:600">₹ ${Number(product.mrp).toFixed(2)}</span>` : ''}
              <span class="gf-cur-price">₹ ${finalPrice.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Render Exact GroFresh Horizontal Product Card HTML (.gf-hcard for Featured Items)
  function renderHorizontalProductCard(product) {
    if (!product) return '';
    const { rawPrice, finalPrice, hasDiscount } = getProductPricing(product);
    const savedAmount = Math.max(0, rawPrice - finalPrice);
    const discountBadge = `-₹ ${savedAmount.toFixed(2)}`;
    const unitLabel = getProductUnitLabel(product);
    const imgUrl = getProductImageUrl(product.image);
    const firstImg = Array.isArray(product.image) ? product.image[0] : product.image;
    const liveFallback = (firstImg && String(firstImg).startsWith('touchstains')) ? imgUrl : `https://getgozo.in/app/storage/app/public/product/${firstImg}`;
    const isWished = wishlist.includes(Number(product.id));
    const detailUrl = `${PAGES_PREFIX}product.html?id=${product.id}`;
    const ratingVal = (Array.isArray(product.rating) && product.rating.length > 0 && product.rating[0].average)
      ? parseFloat(product.rating[0].average).toFixed(1)
      : '0.0';

    return `
      <div class="gf-hcard" data-product-id="${product.id}">
        <div class="gf-hcard-imgbox">
          ${hasDiscount && savedAmount > 0 ? `<span class="gf-discount-pill">${discountBadge}</span>` : ''}
          <a href="${detailUrl}" class="gf-hcard-imglink">
            <img src="${imgUrl}" alt="${escapeHtml(product.name)}" loading="lazy" onerror="if(this.src!=='${liveFallback}'){this.src='${liveFallback}';}else{this.src='${ROOT_PREFIX}assets/image/place_holder.png';}">
          </a>
          <button type="button" class="gf-action-sq gf-hcard-cartbtn" onclick="window.Getgozo.addToCart(${product.id}, 1, null, event)" title="Add to Bag">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
          </button>
        </div>
        <div class="gf-hcard-info">
          <button type="button" class="gf-action-sq gf-hcard-wishbtn ${isWished ? 'active' : ''}" data-wish-btn="${product.id}" onclick="window.Getgozo.toggleWishlist(${product.id}, event)" title="Favourite">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="${isWished ? '#01684B' : 'none'}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>
          <div class="gf-rating-line">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="1">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>${ratingVal}</span>
          </div>
          <a href="${detailUrl}" class="gf-hcard-title" title="${escapeHtml(product.name)}">${escapeHtml(product.name)}</a>
          <div class="gf-unit-text" style="margin:3px 0 5px">${escapeHtml(unitLabel)}</div>
          <div class="gf-price-wrap" style="display:flex;align-items:baseline;gap:6px;flex-wrap:wrap">
            ${Number(product.mrp || 0) > finalPrice ? `<span class="gf-orig-price" style="color:#DC2626;text-decoration:line-through;font-size:12px;font-weight:600">₹ ${Number(product.mrp).toFixed(2)}</span>` : ''}
            <span class="gf-cur-price">₹ ${finalPrice.toFixed(2)}</span>
          </div>
        </div>
      </div>
    `;
  }

  function renderCardActionSlot(product, inCart, outOfStock) {
    if (outOfStock) {
      return `<button type="button" class="card-add-btn" disabled title="Out of Stock">+</button>`;
    }
    if (inCart && inCart.qty > 0) {
      return `
        <div class="card-qty-stepper">
          <button type="button" onclick="window.Getgozo.changeQty(${product.id}, -1, event)">−</button>
          <span>${inCart.qty}</span>
          <button type="button" onclick="window.Getgozo.changeQty(${product.id}, 1, event)">+</button>
        </div>
      `;
    }
    return `<button type="button" class="card-add-btn" onclick="window.Getgozo.addToCart(${product.id}, 1, null, event)" title="${t('add_to_cart', 'Add To Cart')}">+</button>`;
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Cart Operations
  function findProduct(id) {
    return ALL_PRODUCTS.find(p => Number(p.id) === Number(id));
  }

  function saveCart() {
    localStorage.setItem('getgozo_cart', JSON.stringify(cart));
    refreshCartBadgesAndSlots();
    renderCartDrawerItems();
    if (typeof window.onGetgozoCartUpdated === 'function') {
      window.onGetgozoCartUpdated(cart);
    }
  }

  function addToCart(productId, qty = 1, variation = null, ev = null) {
    if (ev) ev.stopPropagation();
    const product = findProduct(productId);
    if (!product) return;

    if (Number(product.total_stock) <= 0) {
      showToast(t('out_of_stock', 'Out of stock!'), 'error');
      return;
    }

    const maxQty = Number(product.maximum_order_quantity || 10);
    const { rawPrice, finalPrice } = getProductPricing(product);
    const unitLabel = variation || getProductUnitLabel(product);
    const imgFile = Array.isArray(product.image) ? product.image[0] : product.image;

    const existing = cart.find(item => Number(item.id) === Number(productId) && item.unit === unitLabel);
    if (existing) {
      if (existing.qty + qty > maxQty) {
        showToast(`Maximum order quantity is ${maxQty}`, 'error');
        return;
      }
      if (existing.qty + qty > Number(product.total_stock)) {
        showToast(`Only ${product.total_stock} left in stock`, 'error');
        return;
      }
      existing.qty += qty;
    } else {
      cart.push({
        id: Number(product.id),
        name: product.name,
        price: finalPrice,
        oldPrice: rawPrice,
        unit: unitLabel,
        image: imgFile,
        qty: qty,
        maxStock: Number(product.total_stock || 10)
      });
    }

    saveCart();
    showToast(`${product.name} added to shopping bag!`, 'success');
  }

  function changeQty(productId, delta, ev = null) {
    if (ev) ev.stopPropagation();
    const idx = cart.findIndex(item => Number(item.id) === Number(productId));
    if (idx === -1) {
      if (delta > 0) addToCart(productId, delta);
      return;
    }
    const product = findProduct(productId);
    // Minimum quantity is always 1 when pressing minus; use removeFromCart (✕) to delete item
    if (delta < 0 && cart[idx].qty <= 1) {
      cart[idx].qty = 1;
      saveCart();
      return;
    }
    const newQty = Math.max(1, cart[idx].qty + delta);
    const maxStock = product ? Math.max(1, Number(product.total_stock || 99)) : 99;
    if (newQty > maxStock) {
      showToast(`Only ${maxStock} available`, 'error');
      return;
    }
    cart[idx].qty = newQty;
    saveCart();
  }

  function removeFromCart(productId, ev = null) {
    if (ev) {
      ev.preventDefault();
      ev.stopPropagation();
    }
    cart = cart.filter(i => Number(i.id) !== Number(productId));
    saveCart();
    showToast('Removed from shopping bag', 'info');
  }

  function clearCart() {
    cart = [];
    saveCart();
  }

  function getCartTotals(deliveryType = 'delivery', couponDiscount = 0) {
    let itemsPrice = 0;
    let oldItemsPrice = 0;
    cart.forEach(i => {
      itemsPrice += i.price * i.qty;
      oldItemsPrice += (i.oldPrice || i.price) * i.qty;
    });
    const productDiscount = Math.max(0, oldItemsPrice - itemsPrice);
    const freeOver = Number(CONFIG.free_delivery_over_amount || 69);
    const baseDelivery = Number(CONFIG.delivery_charge || 100);
    let deliveryFee = 0;
    if (deliveryType === 'delivery' && cart.length > 0) {
      deliveryFee = itemsPrice >= freeOver ? 0 : baseDelivery;
    }
    const total = Math.max(0, itemsPrice - couponDiscount + deliveryFee);
    const earnedPoints = Math.floor(itemsPrice * 0.05); // Up to 5% loyalty points as shown on banner
    return {
      itemsPrice: Math.round(itemsPrice * 100) / 100,
      oldItemsPrice: Math.round(oldItemsPrice * 100) / 100,
      productDiscount: Math.round(productDiscount * 100) / 100,
      couponDiscount: Math.round(couponDiscount * 100) / 100,
      deliveryFee,
      freeOver,
      minOrder: Number(CONFIG.minimum_order_value || 69),
      total: Math.round(total * 100) / 100,
      earnedPoints
    };
  }

  // Wishlist Operations
  function toggleWishlist(productId, ev = null) {
    if (ev) ev.stopPropagation();
    const id = Number(productId);
    const idx = wishlist.indexOf(id);
    const prod = findProduct(id);
    if (idx === -1) {
      wishlist.push(id);
      showToast(`${prod ? prod.name : 'Item'} added to Favourites ❤️`, 'success');
    } else {
      wishlist.splice(idx, 1);
      showToast('Removed from Favourites', 'info');
    }
    localStorage.setItem('getgozo_wishlist', JSON.stringify(wishlist));
    refreshWishlistUI();
    if (typeof window.onGetgozoWishlistUpdated === 'function') {
      window.onGetgozoWishlistUpdated(wishlist);
    }
  }

  function refreshWishlistUI() {
    document.querySelectorAll('.js-wish-count, [data-wishlist-count]').forEach(el => {
      el.textContent = wishlist.length || 0;
      el.style.display = 'flex';
    });
    document.querySelectorAll('[data-wish-btn]').forEach(btn => {
      const id = Number(btn.getAttribute('data-wish-btn'));
      const wished = wishlist.includes(id);
      btn.classList.toggle('wished', wished);
      btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="${wished ? '#01684B' : 'none'}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>`;
    });
  }

  function refreshCartBadgesAndSlots() {
    const count = cart.reduce((acc, i) => acc + i.qty, 0);
    document.querySelectorAll('.js-cart-count, [data-cart-count]').forEach(el => {
      el.textContent = count || 0;
      el.style.display = 'flex';
    });

    document.querySelectorAll('[data-cart-slot]').forEach(slot => {
      const pid = Number(slot.getAttribute('data-cart-slot'));
      const product = findProduct(pid);
      if (product) {
        const inCart = cart.find(i => Number(i.id) === pid);
        const outOfStock = Number(product.total_stock) <= 0;
        slot.innerHTML = renderCardActionSlot(product, inCart, outOfStock);
      }
    });
  }

  // Product Quick View Modal
  function openQuickView(productId, ev = null) {
    if (ev) ev.stopPropagation();
    const p = findProduct(productId);
    if (!p) return;

    const modal = document.getElementById('quickViewProductModal');
    const body = document.getElementById('quickViewModalBody');
    if (!modal || !body) return;

    const { rawPrice, finalPrice, hasDiscount, discountLabel } = getProductPricing(p);
    const unitLabel = getProductUnitLabel(p);
    const imgUrl = getProductImageUrl(p.image);
    const liveFallback = `https://getgozo.in/app/storage/app/public/product/${Array.isArray(p.image) ? p.image[0] : p.image}`;
    const outOfStock = Number(p.total_stock) <= 0;

    body.innerHTML = `
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;align-items:center;">
        <div style="background:#fff;border:1px solid var(--border-color);border-radius:var(--radius-md);padding:18px;text-align:center;position:relative;">
          ${hasDiscount ? `<span class="product-discount-tag">${discountLabel}</span>` : ''}
          <img src="${imgUrl}" alt="${escapeHtml(p.name)}" style="max-height:200px;margin:0 auto;object-fit:contain;" onerror="this.src='${liveFallback}'">
        </div>
        <div>
          <span class="badge ${outOfStock ? 'badge-stock-out' : 'badge-unit'}" style="margin-bottom:8px;">
            ${outOfStock ? t('out_of_stock', 'Out of Stock') : `In Stock (${p.total_stock})`}
          </span>
          <h3 style="font-size:19px;font-weight:700;margin-bottom:4px;">${escapeHtml(p.name)}</h3>
          <div style="font-size:13px;color:var(--text-light);margin-bottom:10px;">Unit / Capacity: <strong>${escapeHtml(unitLabel)}</strong></div>
          <div style="display:flex;align-items:baseline;gap:10px;margin-bottom:14px;">
            <span style="font-size:24px;font-weight:800;color:var(--primary);">₹${finalPrice}</span>
            ${hasDiscount ? `<span style="font-size:14px;color:var(--text-light);text-decoration:line-through;">₹${rawPrice}</span>` : ''}
          </div>
          <div style="font-size:12.5px;color:var(--text-medium);max-height:90px;overflow-y:auto;margin-bottom:16px;">
            ${p.description || `Fresh ${escapeHtml(p.name)} delivered by Getgozo.`}
          </div>
          <div style="display:flex;gap:10px;">
            <button type="button" class="btn btn-primary" style="flex:1;" ${outOfStock ? 'disabled' : ''} onclick="window.Getgozo.addToCart(${p.id}, 1); window.Getgozo.closeModal('quickViewProductModal');">
              🛒 ${t('add_to_cart', 'Add To Cart')}
            </button>
            <a href="${PAGES_PREFIX}product.html?id=${p.id}" class="btn btn-outline">Full Details →</a>
          </div>
        </div>
      </div>
    `;
    openModal('quickViewProductModal');
  }

  // Inject Shared Header, Side Menu Drawer, Slide-out Cart, Modals & Footer
  function injectSharedShell() {
    if (currentLang === 'ar') {
      document.documentElement.setAttribute('dir', 'rtl');
    } else {
      document.documentElement.setAttribute('dir', 'ltr');
    }

    // 1. Preloader
    if (!document.querySelector('.preloader')) {
      const pre = document.createElement('div');
      pre.className = 'preloader';
      pre.innerHTML = `
        <div class="preloader-container">
          <img class="preloader-img" src="${getLogoUrl()}" alt="TOUCHSTAINS Loading" style="width:95px;height:auto;object-fit:contain;">
          <div class="loader"></div>
        </div>
      `;
      document.body.prepend(pre);
      window.addEventListener('load', () => setTimeout(() => pre.classList.add('hidden'), 180));
      setTimeout(() => pre.classList.add('hidden'), 600);
    } else {
      setTimeout(() => document.querySelector('.preloader')?.classList.add('hidden'), 250);
    }

    // 2. Apply saved theme
    const isDark = localStorage.getItem('flutter.theme') === 'true';
    document.body.classList.toggle('theme-dark', isDark);

    // 3. Build Exact GroFresh Header Matching getgozo.in Screenshot
    const currentPath = window.location.pathname;
    const megaCatsHtml = CATEGORIES.map(cat => {
      const detail = CAT_DETAILS[cat.id] || {};
      const childes = detail.childes || [];
      return `
        <div class="gf-cat-item">
          <a href="${PAGES_PREFIX}categories.html?cat=${cat.id}" class="gf-cat-row">
            <img src="${getCategoryImageUrl(cat.image)}" alt="${escapeHtml(cat.name)}" onerror="this.src='${ROOT_PREFIX}assets/image/place_holder.png'">
            <span>${escapeHtml(cat.name)}</span>
            ${childes.length > 0 ? `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto"><path d="M9 18l6-6-6-6"/></svg>` : ''}
          </a>
          ${childes.length > 0 ? `
            <div class="gf-subcat-flyout">
              <div class="gf-subcat-title">${escapeHtml(cat.name)}</div>
              ${childes.map(sub => `
                <a href="${PAGES_PREFIX}categories.html?cat=${cat.id}&sub=${sub.id}" class="gf-subcat-link">
                  <span>${escapeHtml(sub.name)}</span>
                </a>
              `).join('')}
            </div>
          ` : ''}
        </div>
      `;
    }).join('');

    const homeMegaCat = document.getElementById('homeMegaCatDropdown');
    if (homeMegaCat) {
      homeMegaCat.innerHTML = megaCatsHtml;
    }

    const headerPlaceholder = document.getElementById('app-header');
    if (headerPlaceholder) {
      headerPlaceholder.outerHTML = `
        <header class="gf-header" id="siteHeader">
          <div class="gf-container gf-header-inner">
            <div class="gf-header-left">
              <a href="${ROOT_PREFIX}index.html" class="gf-logo" title="TOUCHSTAINS Fabric Stain Remover">
                <img src="${getLogoUrl()}" alt="TOUCHSTAINS" onerror="this.src='${ROOT_PREFIX}assets/image/touchstains-logo.png'">
              </a>
              <nav class="gf-nav">
                <a href="${ROOT_PREFIX}index.html" class="gf-nav-link ${!isSubPage ? 'active' : ''}">${t('home', 'Home')}</a>
                <div class="gf-cat-menu-wrap" id="gfCatMenuWrap">
                  <button type="button" class="gf-nav-link ${currentPath.includes('categories.html') ? 'active' : ''}" onclick="document.getElementById('gfCatMenuWrap').classList.toggle('open')">
                    <span>${t('categories', 'Categories')}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
                  </button>
                  <div class="gf-cat-dropdown">
                    ${megaCatsHtml}
                  </div>
                </div>
              </nav>
            </div>

            <div class="gf-search-wrap header-search-box">
              <form class="gf-search-form" onsubmit="window.Getgozo.submitSearch(event)">
                <input type="text" id="globalSearchInput" class="header-search-input" placeholder="Search for Products" autocomplete="off">
                <button type="submit" class="gf-search-btn" title="Search">
                  <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                </button>
              </form>
              <div class="search-suggestions-dropdown" id="searchSuggestionsDropdown"></div>
            </div>

            <div class="gf-header-icons">
              <a href="${PAGES_PREFIX}wishlist.html" class="gf-icon-btn" title="Favourite List">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span class="gf-badge js-wish-count" data-wishlist-count>0</span>
              </a>

              <button type="button" class="gf-icon-btn" onclick="window.Getgozo.openCartDrawer()" title="${t('shopping_bag', 'Shopping Bag')}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#01684B">
                  <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span class="gf-badge js-cart-count" data-cart-count>0</span>
              </button>

              <div style="position:relative;display:inline-flex;align-items:center;" id="staticAccountMenuWrap">
                <button type="button" class="gf-icon-btn" onclick="event.stopPropagation(); var m=document.getElementById('staticAccountDropdown'); if(m) m.style.display = (m.style.display==='block'?'none':'block');" title="Account: Registration / Login">
                  <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </button>
                <div id="staticAccountDropdown" style="display:none;position:absolute;top:calc(100% + 10px);right:0;width:225px;background:#fff;border:1px solid #E2E8F0;border-radius:14px;box-shadow:0 16px 40px rgba(15,23,42,0.16);padding:8px;z-index:9999;">
                  <div style="padding:8px 10px;border-bottom:1px solid #F1F5F9;margin-bottom:6px;">
                    <strong style="display:block;font-size:13px;color:#0F172A;">${user ? escapeHtml(user.name) : 'TOUCHSTAINS Account'}</strong>
                    <span style="font-size:11px;color:#64748B;">${user ? escapeHtml(user.phone || '') : 'Register or Sign In'}</span>
                  </div>
                  <a href="${PAGES_PREFIX}register.html" style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;text-decoration:none;color:#0F172A;font-size:13px;font-weight:700;">
                    <span style="width:30px;height:30px;border-radius:8px;background:#01684B;color:#fff;display:flex;align-items:center;justify-content:center;">📝</span>
                    <span>Registration</span>
                  </a>
                  <a href="${PAGES_PREFIX}login.html" style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;text-decoration:none;color:#0F172A;font-size:13px;font-weight:700;">
                    <span style="width:30px;height:30px;border-radius:8px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">🔑</span>
                    <span>Login</span>
                  </a>
                  ${user ? `<a href="${PAGES_PREFIX}account.html" style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;text-decoration:none;color:#01684B;font-size:13px;font-weight:700;">
                    <span style="width:30px;height:30px;border-radius:8px;background:#ECFDF5;display:flex;align-items:center;justify-content:center;">👤</span>
                    <span>My Profile</span>
                  </a>` : ''}
                </div>
              </div>

              <button type="button" class="gf-icon-btn gf-hamburger-btn" onclick="window.Getgozo.openMenuDrawer()" title="Menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.6" stroke-linecap="round">
                  <line x1="3" y1="6" x2="21" y2="6"></line>
                  <line x1="3" y1="12" x2="21" y2="12"></line>
                  <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>
        </header>
      `;
    }

    // 4. Build Footer
    const footerPlaceholder = document.getElementById('app-footer');
    if (footerPlaceholder) {
      footerPlaceholder.outerHTML = `
        <footer class="site-footer">
          <div class="newsletter-bar">
            <div class="container newsletter-inner">
              <div class="newsletter-copy">
                <h3>${t('news_letter', 'Newsletter')}</h3>
                <p>${t('subscribe_to_out_new_channel_to_get_latest_updates', 'Subscribe to our newsletter and unlock a world of exclusive offers!')}</p>
              </div>
              <form class="newsletter-form-box" onsubmit="window.Getgozo.subscribeNewsletter(event)">
                <input type="email" required placeholder="${t('your_email_address', 'Your Email Address')}">
                <button type="submit">${t('subscribe', 'Subscribe')}</button>
              </form>
            </div>
          </div>

          <div class="container">
            <div class="footer-main">
              <div class="footer-brand-col">
                <a href="${ROOT_PREFIX}index.html" class="brand-logo" title="TOUCHSTAINS Fabric Stain Remover">
                  <img src="${getLogoUrl()}" alt="TOUCHSTAINS" onerror="this.src='${ROOT_PREFIX}assets/image/touchstains-logo.png'">
                </a>
                <p class="footer-brand-desc">
                  TOUCHSTAINS is your trusted partner for high-performance fabric stain removers and fabric care solutions across India — removing tough rust, ink, paan, haldi, oil, tea, and grease stains instantly.
                </p>
                <ul class="footer-contact-list">
                  <li>
                    <img src="${ROOT_PREFIX}assets/image/location.png" alt="Address">
                    <span>${escapeHtml(CONFIG.ecommerce_address || 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022')} (Pin: 732201)</span>
                  </li>
                  <li>
                    <img src="${ROOT_PREFIX}assets/image/call.png" alt="Phone">
                    <a href="tel:${CONFIG.ecommerce_phone || '+91 7679416294 / 7039214465'}">${CONFIG.ecommerce_phone || '+91 7679416294 / 7039214465'}</a>
                  </li>
                  <li>
                    <img src="${ROOT_PREFIX}assets/image/email.png" alt="Email">
                    <a href="mailto:${CONFIG.ecommerce_email || 'touchstains@gmail.com'}">${CONFIG.ecommerce_email || 'touchstains@gmail.com'}</a>
                  </li>
                </ul>
              </div>

              <div class="footer-col">
                <h4>${t('quick_links', 'Quick Links')}</h4>
                <ul>
                  <li><a href="${ROOT_PREFIX}index.html">${t('home', 'Home')}</a></li>
                  <li><a href="${PAGES_PREFIX}categories.html">${t('all_categories', 'All Categories')}</a></li>
                  <li><a href="${PAGES_PREFIX}shop.html?type=featured">Featured Items</a></li>
                  <li><a href="${PAGES_PREFIX}shop.html?type=daily-needs">${t('daily_needs', 'Daily Needs')}</a></li>
                  <li><a href="${PAGES_PREFIX}shop.html?type=most-reviewed">${t('popular_items', 'Popular items')}</a></li>
                  <li><a href="${PAGES_PREFIX}menus.html">All App Menus</a></li>
                </ul>
              </div>

              <div class="footer-col">
                <h4>My Account</h4>
                <ul>
                  <li><a href="${PAGES_PREFIX}account.html">Profile</a></li>
                  <li><a href="${PAGES_PREFIX}orders.html">${t('my_order', 'My Orders')}</a></li>
                  <li><a href="${PAGES_PREFIX}cart.html">${t('shopping_bag', 'Shopping Bag')}</a></li>
                  <li><a href="${PAGES_PREFIX}wishlist.html">Favourite List</a></li>
                  <li><a href="${PAGES_PREFIX}address.html">${t('address', 'Address')}</a></li>
                  <li><a href="${PAGES_PREFIX}track-order.html">${t('order_track', 'Order Track')}</a></li>
                  <li><a href="${PAGES_PREFIX}settings.html">Settings</a></li>
                </ul>
              </div>

              <div class="footer-col">
                <h4>Help & Policies</h4>
                <ul>
                  <li><a href="${PAGES_PREFIX}contact.html">${t('contact_us', 'Contact Us')}</a></li>
                  <li><a href="${PAGES_PREFIX}faq.html">FAQ</a></li>
                  <li><a href="${PAGES_PREFIX}privacy-policy.html">Privacy Policy</a></li>
                  <li><a href="${PAGES_PREFIX}terms.html">Terms & Conditions</a></li>
                  <li><a href="${PAGES_PREFIX}return-policy.html">Return Policy</a></li>
                  <li><a href="${PAGES_PREFIX}refund-policy.html">Refund Policy</a></li>
                  <li><a href="${PAGES_PREFIX}cancellation-policy.html">Cancellation Policy</a></li>
                </ul>
                <h4 style="margin-top:16px;">${t('download_our_app', 'DOWNLOAD OUR APP')}</h4>
                <div class="store-badges">
                  <a href="https://play.google.com/store/apps/details?id=com.getgozo.user" target="_blank" rel="noopener">
                    <img src="${ROOT_PREFIX}assets/image/play_store.png" alt="Google Play">
                  </a>
                  <a href="#app-store" onclick="window.Getgozo.showToast('iOS App coming soon!','info');return false;">
                    <img src="${ROOT_PREFIX}assets/image/app_store.png" alt="App Store">
                  </a>
                </div>
              </div>
            </div>

            <div class="footer-bottom-bar">
              <span>TOUCHSTAINS @ 2026 • All Rights Reserved | Developed by MS Solutions</span>
              <span>Free Delivery on orders above ₹${CONFIG.free_delivery_over_amount || 69} | Branch: Mumbai Main Hub (PAN India Delivery)</span>
            </div>
          </div>
        </footer>
      `;
    }

    // 5. Inject Side Menu Drawer, Slide-out Cart, Modals, Mobile Bottom Nav & WhatsApp Button
    const shellExtras = document.createElement('div');
    shellExtras.id = 'getgozo-shell-extras';
    shellExtras.innerHTML = `
      <!-- Overlay -->
      <div class="drawer-overlay" id="globalDrawerOverlay" onclick="window.Getgozo.closeAllDrawers()"></div>

      <!-- GroFresh Side Menu Drawer -->
      <aside class="side-menu-drawer" id="sideMenuDrawer">
        <div class="drawer-user-header">
          <img class="drawer-user-avatar" src="${getLogoUrl()}" alt="TOUCHSTAINS" style="width:46px;height:46px;object-fit:contain;background:#fff;border-radius:10px;padding:3px;">
          <div class="drawer-user-info">
            <h4 class="js-user-name">${user ? escapeHtml(user.name) : 'TOUCHSTAINS Guest'}</h4>
            <p class="js-user-phone">${user ? escapeHtml(user.phone) : 'Sion East, Mumbai, Maharashtra - 400022'}</p>
          </div>
          <button type="button" class="drawer-close-btn" onclick="window.Getgozo.closeAllDrawers()">✕</button>
        </div>
        <div class="drawer-menu-list">
          <a href="${ROOT_PREFIX}index.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>${t('home', 'Home')}</span>
          </a>
          <a href="${PAGES_PREFIX}categories.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            <span>${t('all_categories', 'All Categories')}</span>
          </a>
          <a href="${PAGES_PREFIX}cart.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span>${t('shopping_bag', 'Shopping Bag')}</span>
          </a>
          <a href="${PAGES_PREFIX}wishlist.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>Favourite List</span>
          </a>
          <a href="${PAGES_PREFIX}orders.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span>${t('my_order', 'My Orders')}</span>
          </a>
          <a href="${PAGES_PREFIX}track-order.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>${t('order_track', 'Order Track')}</span>
          </a>
          <a href="${PAGES_PREFIX}address.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>${t('address', 'Saved Addresses')}</span>
          </a>
          <a href="${PAGES_PREFIX}notifications.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span>${t('notifications', 'Notifications')}</span>
          </a>
          <a href="${PAGES_PREFIX}chat.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            <span>${t('live_chat', 'Customer Support')}</span>
          </a>
          <a href="${PAGES_PREFIX}settings.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span>Settings</span>
          </a>
          <a href="${PAGES_PREFIX}contact.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            <span>${t('contact_us', 'Contact Us')}</span>
          </a>
          <a href="${PAGES_PREFIX}faq.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>FAQ</span>
          </a>
          <a href="${PAGES_PREFIX}terms.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span>Terms & Conditions</span>
          </a>
          <a href="${PAGES_PREFIX}privacy-policy.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            <span>Privacy Policy</span>
          </a>
          <a href="${PAGES_PREFIX}return-policy.html" class="drawer-menu-link">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            <span>Return & Refund Policy</span>
          </a>
          <a href="${PAGES_PREFIX}register.html" class="drawer-menu-link" style="color:var(--primary);font-weight:700;">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            <span>Customer Registration</span>
          </a>
          <a href="${PAGES_PREFIX}login.html" class="drawer-menu-link" style="color:var(--primary);font-weight:700;">
            <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            <span>${user ? 'My Account / Logout' : 'Login Account'}</span>
          </a>
        </div>
        <div class="drawer-footer-ver" style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
          <img src="${getLogoUrl()}" alt="TOUCHSTAINS" style="height:24px;width:auto;object-fit:contain;background:#fff;padding:2px 6px;border-radius:6px;">
          <span>TOUCHSTAINS Web • v${CONFIG.software_version || '7.7'}</span>
        </div>
      </aside>

      <!-- Slide-Out Shopping Bag Drawer -->
      <aside class="cart-drawer" id="globalCartDrawer">
        <div class="cart-drawer-header">
          <h3><img src="${ROOT_PREFIX}assets/cart_icon.svg" alt="" style="width:22px;"> ${t('shopping_bag', 'Shopping Bag')}</h3>
          <button type="button" class="modal-close-x" style="position:static;" onclick="window.Getgozo.closeAllDrawers()">✕</button>
        </div>
        <div class="cart-drawer-body" id="cartDrawerItemsList"></div>
        <div class="cart-drawer-footer">
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;color:var(--text-medium);">
            <span>Minimum Order: ₹${CONFIG.minimum_order_value || 69}</span>
            <span style="color:var(--primary);font-weight:600;">Free Delivery ≥ ₹${CONFIG.free_delivery_over_amount || 69}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <span style="font-size:15px;font-weight:600;">${t('subtotal', 'Subtotal')}</span>
            <strong id="cartDrawerSubtotal" style="font-size:20px;color:var(--primary);font-weight:800;">₹0</strong>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <a href="${PAGES_PREFIX}cart.html" class="btn btn-outline" style="width:100%;">View Bag</a>
            <a href="${PAGES_PREFIX}checkout.html" class="btn btn-primary" style="width:100%;">Checkout →</a>
          </div>
        </div>
      </aside>

      <!-- Quick View Product Modal -->
      <div class="modal-backdrop" id="quickViewProductModal">
        <div class="modal-card" style="max-width:640px;">
          <button type="button" class="modal-close-x" onclick="window.Getgozo.closeModal('quickViewProductModal')">✕</button>
          <div id="quickViewModalBody"></div>
        </div>
      </div>

      <!-- Location Selector Modal -->
      <div class="modal-backdrop" id="locationModal">
        <div class="modal-card">
          <button type="button" class="modal-close-x" onclick="window.Getgozo.closeModal('locationModal')">✕</button>
          <h3 style="font-size:19px;font-weight:700;margin-bottom:6px;">📍 ${t('select_delivery_address', 'Select Delivery Address')}</h3>
          <p style="font-size:12.5px;color:var(--text-medium);margin-bottom:16px;">
            Active Branch: <strong>Mumbai Main Branch</strong> — Shop No. 3 & 4, Tower B, Braumonte, Sion, Mumbai - 400022 | PAN India Delivery
          </p>
          <div class="form-group">
            <label class="form-label">Select Your Area / Delivery Location</label>
            <select id="locationAreaSelect" class="form-control" onchange="window.Getgozo.selectArea(this.value)">
              <option value="Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022">Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022</option>
              <option value="Dadar, Mumbai (400014)">Dadar, Mumbai (400014)</option>
              <option value="Bandra / BKC, Mumbai (400051)">Bandra / BKC, Mumbai (400051)</option>
              <option value="Andheri, Mumbai (400069)">Andheri, Mumbai (400069)</option>
              <option value="Thane, Maharashtra (400601)">Thane, Maharashtra (400601)</option>
              <option value="PAN India Express Delivery">PAN India Express Delivery</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Or Enter Custom Landmark / Village</label>
            <input type="text" id="customLocationInput" class="form-control" placeholder="e.g., Flat/Shop No., Street Name, City, Pincode">
          </div>
          <button type="button" class="btn btn-primary" style="width:100%;" onclick="window.Getgozo.saveCustomLocation()">
            ${t('save_location', 'Save Location')}
          </button>
        </div>
      </div>

      <!-- Mobile Bottom Bar -->
      <nav class="mobile-bottom-bar">
        <a href="${ROOT_PREFIX}index.html" class="mobile-tab-item ${!isSubPage ? 'active' : ''}">
          <svg class="mob-nav-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>${t('home', 'Home')}</span>
        </a>
        <a href="${PAGES_PREFIX}categories.html" class="mobile-tab-item ${window.location.pathname.includes('categories.html') ? 'active' : ''}">
          <svg class="mob-nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span>${t('categories', 'Categories')}</span>
        </a>
        <a href="${PAGES_PREFIX}cart.html" class="mobile-tab-item ${window.location.pathname.includes('cart.html') ? 'active' : ''}">
          <svg class="mob-nav-icon" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <span>${t('shopping_bag', 'Bag')}</span>
          <span class="header-badge js-cart-count" style="display:none;top:-5px;right:16px;">0</span>
        </a>
        <a href="${PAGES_PREFIX}orders.html" class="mobile-tab-item ${window.location.pathname.includes('orders.html') ? 'active' : ''}">
          <svg class="mob-nav-icon" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span>${t('my_order', 'Orders')}</span>
        </a>
        <a href="${PAGES_PREFIX}menus.html" class="mobile-tab-item ${window.location.pathname.includes('menus.html') ? 'active' : ''}">
          <svg class="mob-nav-icon" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          <span>Menu</span>
        </a>
      </nav>

      <!-- Floating WhatsApp Button (from config.whatsapp: +91 7679416294 / 7039214465) -->
      <a href="https://wa.me/917679416294 / 7039214465?text=Hello%20TOUCHSTAINS!%20I%20want%20to%20order%20stain%20remover." target="_blank" rel="noopener" class="floating-whatsapp" title="Chat on WhatsApp (+91 7679416294 / 7039214465)">
        💬
      </a>

      <!-- Toast Stack -->
      <div class="toast-stack" id="globalToastStack"></div>
    `;
    document.body.appendChild(shellExtras);

    initLiveSearch();
    refreshCartBadgesAndSlots();
    refreshWishlistUI();
    renderCartDrawerItems();
  }

  // Render Cart Drawer Items
  function renderCartDrawerItems() {
    const listEl = document.getElementById('cartDrawerItemsList');
    const subEl = document.getElementById('cartDrawerSubtotal');
    if (!listEl) return;

    const { itemsPrice } = getCartTotals();
    if (subEl) subEl.textContent = `₹${itemsPrice}`;

    if (cart.length === 0) {
      listEl.innerHTML = `
        <div style="text-align:center;padding:50px 16px;">
          <img src="${ROOT_PREFIX}assets/image/empty_order_image.png" alt="Empty Bag" style="width:120px;margin:0 auto 14px;opacity:0.85;">
          <h4 style="font-size:16px;font-weight:700;margin-bottom:6px;">${t('empty_shopping_bag', 'Empty Shopping Bag')}</h4>
          <p style="font-size:13px;color:var(--text-light);margin-bottom:18px;">${t('look_like_you_have_not_added', "Look like you haven't added any item yet.")}</p>
          <a href="${PAGES_PREFIX}shop.html" class="btn btn-primary btn-sm">${t('lets_shop', "Let's Shop")}</a>
        </div>
      `;
      return;
    }

    listEl.innerHTML = cart.map(item => {
      const imgUrl = getProductImageUrl(item.image);
      return `
        <div class="cart-item-row">
          <img src="${imgUrl}" alt="${escapeHtml(item.name)}" onerror="this.src='${ROOT_PREFIX}assets/image/place_holder.png'">
          <div class="cart-item-details">
            <h5><a href="${PAGES_PREFIX}product.html?id=${item.id}">${escapeHtml(item.name)}</a></h5>
            <small>${escapeHtml(item.unit)}</small>
            <div class="price">₹${item.price} × ${item.qty} = ₹${Math.round(item.price * item.qty * 100) / 100}</div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
            <div class="card-qty-stepper">
              <button type="button" onclick="window.Getgozo.changeQty(${item.id}, -1)" ${item.qty <= 1 ? 'disabled style="opacity:0.45;cursor:not-allowed;"' : ''}>−</button>
              <span>${item.qty}</span>
              <button type="button" onclick="window.Getgozo.changeQty(${item.id}, 1)">+</button>
            </div>
            <button type="button" onclick="window.Getgozo.removeFromCart(${item.id}, event)" title="Remove item" style="width:28px;height:28px;border-radius:50%;border:1px solid #FECACA;background:#FEF2F2;color:#DC2626;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">✕</button>
          </div>
        </div>
      `;
    }).join('');
  }

  // Live Search Autocomplete
  function initLiveSearch() {
    const input = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('searchSuggestionsDropdown');
    if (!input || !dropdown) return;

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      if (!q) {
        dropdown.classList.remove('active');
        return;
      }
      const matches = ALL_PRODUCTS.filter(p => p.name.toLowerCase().includes(q)).slice(0, 8);
      if (matches.length === 0) {
        dropdown.innerHTML = `<div style="padding:14px;text-align:center;color:var(--text-light);font-size:13px;">${t('no_result_found', 'No Result Found')}</div>`;
      } else {
        dropdown.innerHTML = matches.map(p => {
          const { finalPrice } = getProductPricing(p);
          return `
            <a href="${PAGES_PREFIX}product.html?id=${p.id}" class="search-suggestion-item">
              <img src="${getProductImageUrl(p.image)}" alt="${escapeHtml(p.name)}" onerror="this.src='${ROOT_PREFIX}assets/image/place_holder.png'">
              <div class="search-suggestion-meta">
                <h6>${escapeHtml(p.name)}</h6>
                <span>₹${finalPrice} • ${escapeHtml(getProductUnitLabel(p))}</span>
              </div>
            </a>
          `;
        }).join('') + `
          <a href="${PAGES_PREFIX}shop.html?search=${encodeURIComponent(input.value.trim())}" style="display:block;padding:10px;text-align:center;font-weight:600;color:var(--primary);font-size:13px;background:var(--primary-light);">
            View all results for "${escapeHtml(input.value.trim())}" →
          </a>
        `;
      }
      dropdown.classList.add('active');
    });

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
  }

  function submitSearch(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('globalSearchInput');
    const q = input ? input.value.trim() : '';
    window.location.href = `${PAGES_PREFIX}shop.html${q ? '?search=' + encodeURIComponent(q) : ''}`;
  }

  // Drawers & Modals
  function openMenuDrawer() {
    document.getElementById('sideMenuDrawer')?.classList.add('open');
    document.getElementById('globalDrawerOverlay')?.classList.add('active');
  }

  function openCartDrawer() {
    renderCartDrawerItems();
    document.getElementById('globalCartDrawer')?.classList.add('open');
    document.getElementById('globalDrawerOverlay')?.classList.add('active');
  }

  function closeAllDrawers() {
    document.getElementById('sideMenuDrawer')?.classList.remove('open');
    document.getElementById('globalCartDrawer')?.classList.remove('open');
    document.getElementById('globalDrawerOverlay')?.classList.remove('active');
  }

  function openModal(id) {
    document.getElementById(id)?.classList.add('active');
  }

  function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
  }

  function selectArea(val) {
    currentLocation = val;
    localStorage.setItem('getgozo_location', val);
    document.querySelectorAll('.js-current-location').forEach(el => el.textContent = val);
    closeModal('locationModal');
    showToast(`Delivery location set to: ${val}`, 'success');
  }

  function saveCustomLocation() {
    const custom = document.getElementById('customLocationInput')?.value.trim();
    const sel = document.getElementById('locationAreaSelect')?.value;
    selectArea(custom || sel || 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022');
  }

  // Theme & Language
  function toggleTheme() {
    const isDark = document.body.classList.toggle('theme-dark');
    localStorage.setItem('flutter.theme', isDark ? 'true' : 'false');
    document.querySelectorAll('.js-theme-icon').forEach(el => el.textContent = isDark ? '🌙' : '☀️');
  }

  function setLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('getgozo_lang', lang);
    window.location.reload();
  }

  function subscribeNewsletter(e) {
    if (e) e.preventDefault();
    showToast('Thank you for subscribing to Getgozo Newsletter! 🎉', 'success');
    e.target.reset();
  }

  // Toast Notification
  function showToast(msg, type = 'success') {
    const stack = document.getElementById('globalToastStack');
    if (!stack) return;
    const item = document.createElement('div');
    item.className = 'toast-msg';
    if (type === 'error') item.style.borderLeftColor = '#FF4D4F';
    item.innerHTML = `
      <span>${type === 'error' ? '⚠️' : '✅'}</span>
      <span>${escapeHtml(msg)}</span>
    `;
    stack.appendChild(item);
    setTimeout(() => {
      item.style.opacity = '0';
      item.style.transform = 'translateX(100%)';
      item.style.transition = 'all 0.25s ease';
      setTimeout(() => item.remove(), 250);
    }, 2800);
  }

  // Export Global API
  window.Getgozo = {
    DATA,
    CONFIG,
    ALL_PRODUCTS,
    CATEGORIES,
    CAT_DETAILS,
    BANNERS,
    ROOT_PREFIX,
    PAGES_PREFIX,
    t,
    escapeHtml,
    getProductImageUrl,
    getCategoryImageUrl,
    getBannerImageUrl,
    getLogoUrl,
    getProductPricing,
    getProductUnitLabel,
    renderProductCard,
    renderHorizontalProductCard,
    findProduct,
    addToCart,
    changeQty,
    removeFromCart,
    clearCart,
    getCart: () => cart,
    getCartTotals,
    toggleWishlist,
    getWishlist: () => wishlist,
    getOrders: () => orders,
    saveOrders: (newOrders) => {
      orders = newOrders;
      localStorage.setItem('getgozo_orders', JSON.stringify(orders));
    },
    getAddresses: () => savedAddresses,
    saveAddresses: (newAddrs) => {
      savedAddresses = newAddrs;
      localStorage.setItem('getgozo_addresses', JSON.stringify(savedAddresses));
    },
    getLoyaltyPoints: () => loyaltyPoints,
    setLoyaltyPoints: (pts) => {
      loyaltyPoints = pts;
      localStorage.setItem('getgozo_loyalty_points', String(pts));
    },
    getWalletBalance: () => walletBalance,
    setWalletBalance: (bal) => {
      walletBalance = bal;
      localStorage.setItem('getgozo_wallet_balance', String(bal));
    },
    getUser: () => user,
    setUser: (u) => {
      user = u;
      if (u) localStorage.setItem('getgozo_user', JSON.stringify(u));
      else localStorage.removeItem('getgozo_user');
    },
    openQuickView,
    openMenuDrawer,
    openCartDrawer,
    closeAllDrawers,
    openModal,
    closeModal,
    selectArea,
    saveCustomLocation,
    toggleTheme,
    setLanguage,
    submitSearch,
    subscribeNewsletter,
    showToast
  };

  
  function initHeroBannerCarousel() {
    const wrap = document.getElementById('heroBannerWrap') || document.querySelector('.gf-banner-carousel-wrap');
    const track = document.getElementById('heroBannerTrack') || (wrap ? wrap.querySelector('.gf-banner-track, .gf-banner-row') : null);
    const dotsContainer = document.getElementById('heroBannerDots') || document.querySelector('.gf-banner-dots');
    if (!wrap || !track) return;

    const originalSlides = Array.from(track.querySelectorAll('.gf-banner-slide:not(.clone)'));
    const N = originalSlides.length;
    if (N <= 1) return;

    track.querySelectorAll('.gf-banner-slide.clone').forEach(el => el.remove());

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
        return w;
      } else {
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
      track.style.transform = 'translateX(' + x + 'px)';
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
      track.style.transform = 'translateX(' + (startOffset + diff) + 'px)';
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

    window.initHeroBannerCarousel = () => {
      updateTransform(false);
      restartAutoplay();
    };

    updateTransform(false);
    startAutoplay();
  }


  // Sitewide Static Registration & Login Gate (matches Laravel Flipkart/Meesho Auth Flow)
  function initStaticAuthGate() {
    const path = window.location.pathname || '';
    if (path.includes('login.html') || path.includes('register.html')) return;

    function isReg() {
      try {
        const raw = localStorage.getItem('touchstains_registered_customer');
        const c = raw ? JSON.parse(raw) : null;
        return Boolean(c && c.registered && c.phone && c.address);
      } catch (e) { return false; }
    }

    document.addEventListener('click', function (e) {
      if (isReg()) return;
      const anchor = e.target.closest('a');
      const href = anchor ? (anchor.getAttribute('href') || '') : '';
      const isProductOrCheckout = href.includes('product.html') || href.includes('checkout.html');
      const isCard = Boolean(e.target.closest('.gf-vcard, .gf-hcard, .product-card'));
      if (isProductOrCheckout || isCard) {
        e.preventDefault();
        e.stopPropagation();
        showToast('🔔 প্রোডাক্ট দেখতে বা অর্ডার করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
        setTimeout(() => {
          window.location.href = PAGES_PREFIX + 'register.html?redirect=checkout';
        }, 700);
      }
    }, true);
  }

  // Legacy compatibility alias
  window.getgozo = window.Getgozo;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      injectSharedShell();
      initStaticAuthGate();
    });
  } else {
    injectSharedShell();
    initHeroBannerCarousel();
    initStaticAuthGate();
  }
})();
