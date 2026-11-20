/**
 * Main JavaScript for FurnitureEcommerse
 */
(function($) {
	'use strict';

	// Cache DOM elements
	const $body = $('body');
	const $window = $(window);
	
	const App = {
		init: function() {
			this.bindEvents();
			this.initWishlist();
			this.initGallery();
			this.initVariationSwatches();
			this.loadWishlistPage();
			this.initPriceSlider();
		},

		initGallery: function() {
			$body.on('click', '.fe-thumb-wrap', function(e) {
				e.preventDefault();
				const $this = $(this);
				const newImgUrl = $this.data('full');
				const newSrcset = $this.data('srcset') || '';
				const $mainImg = $('#fe-main-product-img');

				if (newImgUrl && $mainImg.length) {
					$mainImg.attr('src', newImgUrl);
					if (newSrcset) {
						$mainImg.attr('srcset', newSrcset);
					} else {
						$mainImg.removeAttr('srcset');
					}
					$this.addClass('active').siblings().removeClass('active');
				}
			});

			// Variation change listener for single product
			$body.on('found_variation', '.variations_form', function(e, variation) {
				if (variation) {
					// 1. Update image
					if (variation.image && (variation.image.full_src || variation.image.src)) {
						const $mainImg = $('#fe-main-product-img');
						const imgUrl = variation.image.full_src || variation.image.src;
						if ($mainImg.length) {
							$mainImg.attr('src', imgUrl).removeAttr('srcset');
						}
					}
					// 2. Update price
					if (variation.price_html) {
						$('.fe-product-price').html(variation.price_html);
					}
				}
			});

			// Reset variation listener
			$body.on('reset_data', '.variations_form', function() {
				$('.fe-color-swatch-item, .fe-size-pill-item').removeClass('active');
			});
		},

		// Interactive Visual Color Swatches & Size Pills for Single Product
		initVariationSwatches: function() {
			const $form = $('.variations_form');
			if (!$form.length) return;

			// Color Swatches (pa_color)
			const $colorSelect = $form.find('select#pa_color');
			if ($colorSelect.length && !$form.find('.fe-color-swatches-wrap').length) {
				const colorMap = {
					'black': '#242424',
					'white': '#ffffff',
					'brown': '#8B4513',
					'gray': '#9e9e9e',
					'beige': '#d4c5a9'
				};
				let swatchHtml = '<div class="fe-variation-swatches fe-color-swatches-wrap">';
				$colorSelect.find('option').each(function() {
					const val = $(this).val();
					const text = $(this).text();
					if (!val) return;
					const bg = colorMap[val.toLowerCase()] || '#cccccc';
					const isActive = $colorSelect.val() === val ? ' active' : '';
					swatchHtml += '<span class="fe-color-swatch-item' + isActive + '" data-val="' + val + '" title="' + text + '"><span class="fe-color-swatch-circle" style="background-color:' + bg + ';"></span></span>';
				});
				swatchHtml += '</div>';
				$colorSelect.after(swatchHtml);
				$colorSelect.hide();
			}

			// Size Pills (pa_size)
			const $sizeSelect = $form.find('select#pa_size');
			if ($sizeSelect.length && !$form.find('.fe-size-pills-wrap').length) {
				let sizeHtml = '<div class="fe-variation-swatches fe-size-pills-wrap">';
				$sizeSelect.find('option').each(function() {
					const val = $(this).val();
					const text = $(this).text();
					if (!val) return;
					const isActive = $sizeSelect.val() === val ? ' active' : '';
					sizeHtml += '<span class="fe-size-pill-item' + isActive + '" data-val="' + val + '">' + text.toUpperCase() + '</span>';
				});
				sizeHtml += '</div>';
				$sizeSelect.after(sizeHtml);
				$sizeSelect.hide();
			}

			// Click handler for color swatches
			$form.on('click', '.fe-color-swatch-item', function() {
				const $this = $(this);
				const val = $this.data('val');
				$this.addClass('active').siblings().removeClass('active');
				$form.find('select#pa_color').val(val).trigger('change');
			});

			// Click handler for size pills
			$form.on('click', '.fe-size-pill-item', function() {
				const $this = $(this);
				const val = $this.data('val');
				$this.addClass('active').siblings().removeClass('active');
				$form.find('select#pa_size').val(val).trigger('change');
			});
		},

		bindEvents: function() {
			// Header actions
			$body.on('click', '.fe-search-toggle', this.toggleSearch);
			$body.on('click', '.fe-cart-toggle', this.toggleCart);
			$body.on('click', '.fe-mobile-toggle', this.toggleMobileMenu);
			$body.on('click', '.fe-modal__close, .fe-modal__overlay, .fe-cart-drawer__close, .fe-cart-drawer__overlay, .fe-mobile-overlay__close, .fe-mobile-overlay__backdrop', this.closeModals);

			// AJAX Live Search
			$('#fe-search-input').on('input', this.debounce(this.liveSearch, 500));

			// Quick View
			$body.on('click', '.fe-quickview-btn', this.openQuickView);

			// Add to Cart
			$body.on('click', '.fe-addtocart-btn', this.addToCart);

			// Mini Cart Remove
			$body.on('click', '.fe-mini-cart-item__remove', this.removeMiniCartItem);

			// Quantity controls
			$body.on('click', '.fe-qty-minus', this.qtyMinus);
			$body.on('click', '.fe-qty-plus', this.qtyPlus);
			
			// Variations in Quick View
			$body.on('click', '.fe-qv-variations .fe-swatch', this.selectVariation);

			// AJAX Filter
			$('.fe-filter-form input, .fe-filter-form select').on('change', this.filterProducts);

			// Homepage Tabs & Hotspots (WoodMart Furniture 2)
			$body.on('click', '.fe-tab-btn', this.filterHomepageTabs);
			$body.on('click', '.fe-hotspot__pin', this.toggleHotspot);
			$(document).on('click', this.closeHotspotsOutside);

			// Mobile Shop Filter Sidebar
			$body.on('click', '#fe-mobile-filter-btn', function(e) {
				e.preventDefault();
				$('#fe-shop-sidebar').addClass('active');
			});
			$body.on('click', '#fe-sidebar-close', function(e) {
				e.preventDefault();
				$('#fe-shop-sidebar').removeClass('active');
			});

			// Single Product Buy Now
			$body.on('click', '.fe-buy-now-btn', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const $form = $btn.closest('form.cart');
				if (!$form.length) return;

				if ($form.hasClass('variations_form')) {
					const varId = parseInt($form.find('input[name="variation_id"]').val());
					if (!varId || varId === 0) {
						alert('Please select product options before proceeding.');
						return false;
					}
				}

				$form.find('input[name="fe_buy_now"]').val('1');

				const prodId = $btn.data('product-id') || $form.find('[name="product_id"]').val() || $form.find('button[name="add-to-cart"]').val();
				if (!$form.find('input[type="hidden"][name="add-to-cart"]').length && prodId) {
					$form.append('<input type="hidden" name="add-to-cart" value="' + prodId + '">');
				}

				$btn.addClass('loading').prop('disabled', true);
				$form.submit();
			});

			// My Account Mobile Toggle
			$body.on('click', '#fe-account-nav-toggle', function(e) {
				e.preventDefault();
				const $nav = $(this).closest('.fe-account-navigation');
				const $list = $nav.find('.fe-account-nav-list');
				$nav.toggleClass('is-open');
				$list.slideToggle(200);
			});

			// My Account Auth Tabs Switcher
			$body.on('click', '.fe-auth-tab', function(e) {
				e.preventDefault();
				const $tab = $(this);
				const targetId = $tab.data('target');
				$('.fe-auth-tab').removeClass('is-active').attr('aria-selected', 'false');
				$tab.addClass('is-active').attr('aria-selected', 'true');
				$('.fe-auth-pane').removeClass('is-active');
				$('#' + targetId).addClass('is-active');

				if (window.history && window.history.pushState) {
					const action = (targetId === 'fe-auth-register-pane') ? 'register' : 'login';
					const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname + '?action=' + action;
					window.history.pushState({path: newUrl}, '', newUrl);
				}
			});

			$body.on('click', '.fe-auth-switch-link', function(e) {
				e.preventDefault();
				const targetId = $(this).data('target');
				const tabId = (targetId === 'fe-auth-register-pane') ? '#fe-tab-register' : '#fe-tab-login';
				$(tabId).trigger('click');
			});
		},

		// Homepage Tab Filtering
		filterHomepageTabs: function(e) {
			e.preventDefault();
			const $btn = $(this);
			const filter = $btn.data('filter');
			$btn.addClass('active').siblings().removeClass('active');

			const $grid = $('#fe-tabbed-product-grid');
			const $cards = $grid.find('.fe-product-card');

			if (filter === 'all') {
				$cards.stop().fadeIn(250);
			} else {
				$cards.each(function() {
					const $card = $(this);
					const categories = ($card.data('category') || '').toString().toLowerCase();
					if (categories.indexOf(filter.toLowerCase()) !== -1) {
						$card.stop().fadeIn(250);
					} else {
						$card.stop().fadeOut(150);
					}
				});
			}
		},

		// Lookbook Hotspots
		toggleHotspot: function(e) {
			e.preventDefault();
			e.stopPropagation();
			const $hotspot = $(this).closest('.fe-hotspot');
			$hotspot.toggleClass('active').siblings('.fe-hotspot').removeClass('active');
		},

		closeHotspotsOutside: function(e) {
			if (!$(e.target).closest('.fe-hotspot').length) {
				$('.fe-hotspot').removeClass('active');
			}
		},

		// Modals & Drawers
		toggleSearch: function(e) {
			e.preventDefault();
			$('#fe-search-modal').addClass('active');
			setTimeout(() => $('#fe-search-input').focus(), 100);
		},

		toggleCart: function(e) {
			e.preventDefault();
			const $drawer = $('#fe-cart-drawer');
			$drawer.addClass('active');
			
			// Fetch cart contents
			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: { action: 'fe_get_mini_cart', nonce: feData.nonce },
				success: function(response) {
					$drawer.find('.fe-cart-drawer__body').html(response);
				}
			});
		},

		toggleMobileMenu: function(e) {
			e.preventDefault();
			$('#fe-mobile-menu-overlay').addClass('active');
		},

		closeModals: function(e) {
			if (e) e.preventDefault();
			$('.fe-modal, .fe-cart-drawer, .fe-mobile-overlay').removeClass('active');
		},

		// AJAX Live Search
		liveSearch: function(e) {
			const query = $(e.target).val().trim();
			const $results = $('#fe-search-results');

			if (query.length < 2) {
				$results.empty();
				return;
			}

			$results.html('<div class="fe-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_live_search',
					nonce: feData.nonce,
					query: query
				},
				success: function(response) {
					if (response.success) {
						let html = '';
						
						if (response.data.products.length > 0) {
							html += '<h3>Products</h3>';
							response.data.products.forEach(p => {
								html += `
									<a href="${p.url}" class="fe-search-item">
										<img src="${p.image}" alt="${p.title}">
										<div class="fe-search-item__info">
											<h4>${p.title}</h4>
											<span class="fe-search-item__price">${p.price}</span>
										</div>
									</a>
								`;
							});
						}

						if (response.data.posts.length > 0) {
							html += '<h3>Articles</h3>';
							response.data.posts.forEach(p => {
								html += `
									<a href="${p.url}" class="fe-search-item">
										<div class="fe-search-item__info">
											<h4>${p.title}</h4>
											<span class="fe-search-item__price" style="font-weight:normal; color:#888;">${p.date}</span>
										</div>
									</a>
								`;
							});
						}

						if (html === '') {
							html = '<div class="fe-no-results">No results found for "'+query+'"</div>';
						}

						$results.html(html);
					}
				}
			});
		},

		// Quick View
		openQuickView: function(e) {
			e.preventDefault();
			const productId = $(this).data('product-id');
			const $modal = $('#fe-quickview-modal');
			const $body = $modal.find('.fe-modal__body');

			$modal.addClass('active');
			$body.html('<div class="fe-modal__loading"><i class="fas fa-spinner fa-spin"></i></div>');

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_quick_view',
					nonce: feData.nonce,
					product_id: productId
				},
				success: function(response) {
					if (response.success) {
						$body.html(response.data.html);
						// Bind gallery thumbs
						$body.find('.fe-qv-thumb').on('click', function() {
							const src = $(this).data('full');
							$('#fe-qv-main-image').attr('src', src);
							$(this).addClass('active').siblings().removeClass('active');
						});
					} else {
						$body.html('<div class="fe-error">Error loading product details.</div>');
					}
				}
			});
		},

		// Variations logic
		selectVariation: function() {
			const $this = $(this);
			const $container = $this.closest('.fe-qv-variations');
			const $group = $this.closest('.fe-qv-variation-options');
			
			$this.addClass('active').siblings().removeClass('active');
			
			// Update label
			const val = $this.data('value');
			$group.siblings('.fe-qv-variation-label').find('.fe-qv-selected-value').text($this.attr('title') || val);

			// Check if all attributes selected
			const allSelected = $container.find('.fe-qv-variation-options').length === $container.find('.fe-swatch.active').length;
			
			if (allSelected) {
				const variations = JSON.parse($container.attr('data-variations'));
				let selectedAttrs = {};
				
				$container.find('.fe-qv-variation-options').each(function() {
					const attrName = 'attribute_' + $(this).data('attribute');
					const attrVal = $(this).find('.fe-swatch.active').data('value');
					selectedAttrs[attrName] = attrVal;
				});

				// Find matching variation
				const match = variations.find(v => {
					let isMatch = true;
					for (let key in selectedAttrs) {
						if (v.attributes[key] !== '' && v.attributes[key] !== selectedAttrs[key]) {
							isMatch = false;
							break;
						}
					}
					return isMatch;
				});

				const $btn = $container.find('.fe-qv-add-variation-btn');
				const $price = $container.find('.fe-qv-variation-price');

				if (match && match.is_in_stock) {
					$price.html(match.price_html);
					$btn.prop('disabled', false).html('<i class="fas fa-shopping-bag"></i> Add to Cart')
						.data('variation-id', match.variation_id);
					
					// Update main image if variation has one
					if (match.image.full_src) {
						$('#fe-qv-main-image').attr('src', match.image.full_src);
					}
				} else {
					$price.html('<span style="color:red;">Out of stock</span>');
					$btn.prop('disabled', true).html('Unavailable');
				}
			}
		},

		// AJAX Add to Cart
		addToCart: function(e) {
			e.preventDefault();
			const $btn = $(this);
			const productId = $btn.data('product-id');
			const qty = $btn.closest('.fe-product-card, .fe-qv-info').find('.fe-qty-input').val() || 1;

			$btn.addClass('loading').prop('disabled', true);
			const originalHtml = $btn.html();
			$btn.html('<i class="fas fa-spinner fa-spin"></i> Adding...');

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_add_to_cart',
					nonce: feData.nonce,
					product_id: productId,
					quantity: qty
				},
				success: function(response) {
					$btn.removeClass('loading').prop('disabled', false).html(originalHtml);
					
					if (response.success) {
						App.showToast(response.data.message);
						
						// Update cart fragments
						$('.fe-cart-count').text(response.data.cartCount);
						$('.fe-cart-drawer__body').html(response.data.miniCart);
						
						// Open drawer
						setTimeout(() => $('#fe-cart-drawer').addClass('active'), 500);
					} else {
						App.showToast(response.data.message || 'Error adding to cart', 'error');
					}
				}
			});
		},

		// Mini cart remove
		removeMiniCartItem: function(e) {
			e.preventDefault();
			const $btn = $(this);
			const cartItemKey = $btn.data('cart-item');
			const $drawer = $('#fe-cart-drawer');
			const $item = $btn.closest('.fe-mini-cart-item');
			
			$btn.html('<i class="fas fa-spinner fa-spin"></i>');
			$item.css('opacity', '0.5');

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_remove_mini_cart_item',
					nonce: feData.nonce,
					cart_item_key: cartItemKey
				},
				success: function(response) {
					if (response.success) {
						$('.fe-cart-count').text(response.data.cartCount);
						$drawer.find('.fe-cart-drawer__body').html(response.data.miniCart);
						App.showToast(response.data.message || 'Item removed from cart');
					} else {
						App.showToast(response.data.message || 'Error removing item', 'error');
						$btn.html('<i class="fas fa-times"></i>');
						$item.css('opacity', '1');
					}
				},
				error: function() {
					App.showToast('Network error removing item', 'error');
					$btn.html('<i class="fas fa-times"></i>');
					$item.css('opacity', '1');
				}
			});
		},

		// Wishlist
		initWishlist: function() {
			// Toggle wishlist
			$body.on('click', '.fe-wishlist-btn', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const productId = $btn.data('product-id');
				
				$btn.find('i').removeClass('far').addClass('fas fa-spinner fa-spin');

				$.ajax({
					url: feData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'fe_toggle_wishlist',
						nonce: feData.nonce,
						product_id: productId
					},
					success: function(response) {
						if (response.success) {
							if (response.data.guest) {
								// Handle localStorage for guest
								let wishlist = JSON.parse(localStorage.getItem('fe_wishlist') || '[]');
								const idx = wishlist.indexOf(productId);
								if (idx > -1) {
									wishlist.splice(idx, 1);
									$btn.find('i').removeClass('fas fa-spinner fa-spin').addClass('far fa-heart');
									App.showToast('Removed from wishlist');
								} else {
									wishlist.push(productId);
									$btn.find('i').removeClass('fa-spinner fa-spin far').addClass('fas fa-heart');
									App.showToast('Added to wishlist');
								}
								localStorage.setItem('fe_wishlist', JSON.stringify(wishlist));
								$('.fe-wishlist-count').text(wishlist.length);
							} else {
								// Handled by DB
								if (response.data.action === 'added') {
									$btn.find('i').removeClass('fa-spinner fa-spin far').addClass('fas fa-heart');
								} else {
									$btn.find('i').removeClass('fas fa-spinner fa-spin').addClass('far fa-heart');
								}
								App.showToast(response.data.message);
								$('.fe-wishlist-count').text(response.data.count);
							}
						}
					}
				});
			});

			// Check wishlist status on load
			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_get_wishlist_ids',
					nonce: feData.nonce
				},
				success: function(response) {
					if (response.success) {
						let ids = response.data.ids;
						if (response.data.guest) {
							ids = JSON.parse(localStorage.getItem('fe_wishlist') || '[]');
							$('.fe-wishlist-count').text(ids.length);
						} else {
							$('.fe-wishlist-count').text(response.data.count);
						}

						ids.forEach(id => {
							$(`.fe-wishlist-btn[data-product-id="${id}"] i`).removeClass('far').addClass('fas');
						});
					}
				}
			});
		},

		// Wishlist Page Content Loader
		loadWishlistPage: function() {
			const $page = $('#fe-wishlist-page');
			if (!$page.length) return;

			const $loading = $page.find('.fe-wishlist-loading');
			const $content = $page.find('.fe-wishlist-content');
			const $empty = $page.find('.fe-wishlist-empty');

			let guestIds = [];
			try {
				guestIds = JSON.parse(localStorage.getItem('fe_wishlist') || '[]');
			} catch (err) {
				guestIds = [];
			}

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'fe_get_wishlist',
					nonce: feData.nonce,
					ids: guestIds
				},
				success: function(response) {
					$loading.hide();
					if (response.success && response.data.products && response.data.products.length > 0) {
						let html = `
						<table class="fe-wishlist-table">
							<thead>
								<tr>
									<th class="fe-wishlist-col-remove"></th>
									<th class="fe-wishlist-col-img"></th>
									<th class="fe-wishlist-col-name">Product</th>
									<th class="fe-wishlist-col-price">Unit Price</th>
									<th class="fe-wishlist-col-stock">Stock</th>
									<th class="fe-wishlist-col-action">Action</th>
								</tr>
							</thead>
							<tbody>
						`;

						response.data.products.forEach(p => {
							const stockClass = p.in_stock ? 'fe-stock-in' : 'fe-stock-out';
							const actionBtn = p.is_variable
								? `<a href="${p.url}" class="fe-btn fe-btn--secondary fe-btn--sm">Select Options</a>`
								: (p.in_stock
									? `<button type="button" class="fe-btn fe-btn--primary fe-btn--sm fe-addtocart-btn" data-product-id="${p.id}">Add to Cart</button>`
									: `<button type="button" class="fe-btn fe-btn--disabled fe-btn--sm" disabled>Out of Stock</button>`);

							html += `
								<tr class="fe-wishlist-row" data-product-id="${p.id}">
									<td class="fe-wishlist-col-remove">
										<button type="button" class="fe-wishlist-item-remove" data-product-id="${p.id}" title="Remove">&times;</button>
									</td>
									<td class="fe-wishlist-col-img">
										<a href="${p.url}"><img src="${p.image}" alt="${p.name}"></a>
									</td>
									<td class="fe-wishlist-col-name">
										<a href="${p.url}" class="fe-wishlist-product-title">${p.name}</a>
									</td>
									<td class="fe-wishlist-col-price">
										<div class="fe-wishlist-price">${p.price}</div>
									</td>
									<td class="fe-wishlist-col-stock">
										<span class="fe-stock-badge ${stockClass}">${p.stock_text}</span>
									</td>
									<td class="fe-wishlist-col-action">
										${actionBtn}
									</td>
								</tr>
							`;
						});

						html += `</tbody></table>`;
						$content.html(html).show();
						$empty.hide();
					} else {
						$content.hide();
						$empty.show();
					}
				},
				error: function() {
					$loading.hide();
					$empty.show();
				}
			});

			// Remove item handler
			$page.on('click', '.fe-wishlist-item-remove', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const pid = parseInt($btn.data('product-id'));
				const $row = $btn.closest('tr');

				let wishlist = [];
				try {
					wishlist = JSON.parse(localStorage.getItem('fe_wishlist') || '[]');
				} catch (err) {}
				const idx = wishlist.indexOf(pid);
				if (idx > -1) {
					wishlist.splice(idx, 1);
					localStorage.setItem('fe_wishlist', JSON.stringify(wishlist));
				}

				$.ajax({
					url: feData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'fe_toggle_wishlist',
						nonce: feData.nonce,
						product_id: pid
					}
				});

				$row.fadeOut(300, function() {
					$row.remove();
					const remaining = $page.find('.fe-wishlist-row').length;
					$('.fe-wishlist-count').text(remaining);
					if (remaining === 0) {
						$content.hide();
						$empty.show();
					}
				});
				App.showToast('Removed from wishlist');
			});
		},

		// AJAX Filter
		filterProducts: function() {
			const $form = $('.fe-filter-form');
			const $grid = $('.fe-shop-products-wrap');
			
			$grid.append('<div class="fe-loading-overlay"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

			const formData = $form.serialize();

			$.ajax({
				url: feData.ajaxUrl,
				type: 'POST',
				data: formData + '&action=fe_filter_products&nonce=' + feData.nonce,
				success: function(response) {
					if (response.success) {
						$('.fe-product-grid').html(response.data.html);
						$('.fe-shop-result-count').text(`Showing ${response.data.count} results`);
						// Re-init any product card JS (like sliders) if needed
					}
					$grid.find('.fe-loading-overlay').remove();
				}
			});
		},

		// Utilities
		qtyMinus: function() {
			const $input = $(this).siblings('input');
			let val = parseInt($input.val());
			if (val > 1) {
				$input.val(val - 1).trigger('change');
			}
		},

		qtyPlus: function() {
			const $input = $(this).siblings('input');
			let val = parseInt($input.val());
			const max = parseInt($input.attr('max')) || 99;
			if (val < max) {
				$input.val(val + 1).trigger('change');
			}
		},

		showToast: function(msg, type = 'success') {
			const $toast = $('#fe-toast');
			$toast.text(msg).css('background', type === 'error' ? '#d9534f' : '#1a1a2e').addClass('active');
			setTimeout(() => $toast.removeClass('active'), 3000);
		},

		// Dual Price Slider
		initPriceSlider: function() {
			const $rangeMin = $('#fe-range-min');
			const $rangeMax = $('#fe-range-max');
			const $bar = $('#fe-price-slider-bar');
			const $amount = $('#fe-price-amount');
			const $inputMin = $('#fe-min-price-input');
			const $inputMax = $('#fe-max-price-input');

			if (!$rangeMin.length || !$rangeMax.length) return;

			const min = parseFloat($rangeMin.attr('min')) || 0;
			const max = parseFloat($rangeMax.attr('max')) || 1500;
			const minGap = 5;

			const updateSlider = function(triggerChange) {
				let valMin = parseFloat($rangeMin.val());
				let valMax = parseFloat($rangeMax.val());

				if (valMax - valMin < minGap) {
					if ($(this).is($rangeMin)) {
						valMin = Math.max(min, valMax - minGap);
						$rangeMin.val(valMin);
					} else {
						valMax = Math.min(max, valMin + minGap);
						$rangeMax.val(valMax);
					}
				}

				const rangeSpan = (max - min) || 1;
				const percentMin = Math.max(0, Math.min(100, ((valMin - min) / rangeSpan) * 100));
				const percentMax = Math.max(0, Math.min(100, ((valMax - min) / rangeSpan) * 100));

				$bar.css({
					'left': percentMin + '%',
					'width': (percentMax - percentMin) + '%'
				});

				$amount.text('$' + Math.round(valMin) + ' — $' + Math.round(valMax));
				$inputMin.val(valMin);
				$inputMax.val(valMax);

				if (triggerChange) {
					$inputMin.trigger('change');
				}
			};

			// Initial position
			updateSlider.call($rangeMin[0], false);

			$rangeMin.on('input', function() { updateSlider.call(this, false); });
			$rangeMax.on('input', function() { updateSlider.call(this, false); });

			$rangeMin.on('change', function() { updateSlider.call(this, true); });
			$rangeMax.on('change', function() { updateSlider.call(this, true); });
		},

		debounce: function(func, wait) {
			let timeout;
			return function(...args) {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, args), wait);
			};
		}
	};

	$(document).ready(function() {
		App.init();
	});

})(jQuery);
