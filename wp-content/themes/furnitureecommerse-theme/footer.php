<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #page div and all content after.
 *
 * @package FurnitureEcommerse
 */

?>
	<footer class="fe-footer">
		<div class="fe-container">
			<div class="fe-footer-top">
				
				<!-- Column 1: About -->
				<div class="fe-footer-about">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<h2 style="color:white; margin-bottom:15px;"><?php bloginfo( 'name' ); ?></h2>
					<?php endif; ?>
					<p>Transforming houses into homes with minimalist Scandinavian design. Crafted for life.</p>
					<div class="fe-socials">
						<a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
						<a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
						<a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
						<a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
					</div>
				</div>

				<!-- Column 2: Customer Service -->
				<div class="fe-footer-links">
					<h4 class="fe-footer-title">Customer Service</h4>
					<ul>
						<li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>">Contact Us</a></li>
						<li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">Track Your Order</a></li>
						<li><a href="#">Shipping & Delivery</a></li>
						<li><a href="#">Returns & Exchanges</a></li>
						<li><a href="#">Frequently Asked Questions</a></li>
					</ul>
				</div>

				<!-- Column 3: Information -->
				<div class="fe-footer-links">
					<h4 class="fe-footer-title">Information</h4>
					<ul>
						<li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about-us' ) ) ); ?>">About Us</a></li>
						<li><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Store Directory</a></li>
						<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Journal & Editorial</a></li>
						<li><a href="#">Privacy Policy</a></li>
						<li><a href="#">Terms & Conditions</a></li>
					</ul>
				</div>

				<!-- Column 4: Newsletter -->
				<div class="fe-footer-newsletter">
					<h4 class="fe-footer-title">Join Our Newsletter</h4>
					<p style="color:#a0a0b0; margin-bottom:15px;">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
					<form class="fe-newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed!');">
						<input type="email" placeholder="Enter your email" required>
						<button type="submit">Subscribe</button>
					</form>
				</div>

			</div>

			<div class="fe-footer-bottom">
				<div class="fe-copyright">
					&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All Rights Reserved.
				</div>
				<div class="fe-payments">
					<i class="fab fa-cc-visa fa-2x"></i> &nbsp;
					<i class="fab fa-cc-mastercard fa-2x"></i> &nbsp;
					<i class="fab fa-cc-paypal fa-2x"></i>
				</div>
			</div>
		</div>
	</footer>

	<!-- Search Modal -->
	<div id="fe-search-modal" class="fe-modal fe-modal--search">
		<div class="fe-modal__overlay"></div>
		<div class="fe-modal__container fe-search-container">
			<div class="fe-search-form">
				<i class="fas fa-search fe-search-icon"></i>
				<input type="text" id="fe-search-input" placeholder="Search for products, categories, or posts...">
				<button class="fe-modal__close" title="Close"><i class="fas fa-times"></i></button>
			</div>
			<div id="fe-search-results" class="fe-search-results"></div>
		</div>
	</div>

	<!-- Cart Drawer -->
	<div id="fe-cart-drawer" class="fe-cart-drawer">
		<div class="fe-cart-drawer__overlay"></div>
		<div class="fe-cart-drawer__panel">
			<div class="fe-cart-drawer__header">
				<h3>Shopping Cart</h3>
				<button class="fe-cart-drawer__close"><i class="fas fa-times"></i></button>
			</div>
			<div class="fe-cart-drawer__body">
				<!-- Mini cart will load here via AJAX -->
				<div style="padding: 60px 20px; text-align: center; color: var(--fe-color-text-muted);">
					<i class="fas fa-spinner fa-spin fa-2x" style="color: var(--fe-color-primary); margin-bottom: 12px; display: block;"></i>
					<span>Loading cart...</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Quick View Modal -->
	<div id="fe-quickview-modal" class="fe-modal fe-modal--quickview">
		<div class="fe-modal__overlay"></div>
		<div class="fe-modal__container">
			<button class="fe-modal__close"><i class="fas fa-times"></i></button>
			<div class="fe-modal__body">
				<!-- Content loads here -->
			</div>
		</div>
	</div>

	<!-- Mobile Menu Overlay -->
	<div id="fe-mobile-menu-overlay" class="fe-mobile-overlay">
		<div class="fe-mobile-overlay__backdrop"></div>
		<div class="fe-mobile-overlay__panel">
			<div class="fe-mobile-overlay__header">
				<div class="fe-mobile-logo"><?php bloginfo('name'); ?></div>
				<button class="fe-mobile-overlay__close"><i class="fas fa-times"></i></button>
			</div>
			<div class="fe-mobile-nav">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'fe-mobile-menu-list',
					) );
				}
				?>
			</div>
			<div class="fe-mobile-nav-footer">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<i class="far fa-user"></i> My Account
				</a>
				<a href="<?php echo esc_url( site_url( '/wishlist/' ) ); ?>">
					<i class="far fa-heart"></i> Wishlist (<span class="fe-wishlist-count">0</span>)
				</a>
			</div>
		</div>
	</div>

	<!-- Toast -->
	<div id="fe-toast" class="fe-toast"></div>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
