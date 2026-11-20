<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <main>
 *
 * @package FurnitureEcommerse
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'flavflavor' ); ?></a>

	<header class="fe-header">
		<!-- WoodMart Announcement Topbar -->
		<div class="fe-topbar">
			<div class="fe-container">
				<div class="fe-topbar__inner">
					<div class="fe-topbar__left">
						<span class="fe-topbar__announcement">
							<i class="fas fa-truck-fast"></i> Free shipping for all orders over $130!
						</span>
					</div>
					<div class="fe-topbar__right">
						<a href="tel:6864921044" class="fe-topbar__link">
							<i class="fas fa-phone-volume"></i> (686) 492-1044
						</a>
						<span class="fe-topbar__divider">|</span>
						<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="fe-topbar__link">Showrooms</a>
						<span class="fe-topbar__divider">|</span>
						<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="fe-topbar__link">About us</a>
					</div>
				</div>
			</div>
		</div>

		<div class="fe-container">
			<div class="fe-header__inner">
				
				<!-- Mobile Menu Toggle -->
				<button class="fe-action-icon fe-mobile-toggle" aria-label="Menu">
					<i class="fas fa-bars"></i>
				</button>

				<!-- Logo -->
				<div class="fe-header__logo">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="fe-header__logo-text">
							<?php bloginfo( 'name' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<!-- Main Navigation (Desktop) -->
				<nav class="fe-main-nav">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'container'      => false,
							'walker'         => new FE_Mega_Menu_Walker(),
							'fallback_cb'    => false,
						) );
					}
					?>
				</nav>

				<!-- Header Actions -->
				<div class="fe-header__actions">
					<!-- Search -->
					<button class="fe-action-icon fe-search-toggle" aria-label="Search">
						<i class="fas fa-search"></i>
					</button>

					<!-- Account -->
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="fe-action-icon" aria-label="My Account">
						<i class="far fa-user"></i>
					</a>

					<!-- Wishlist -->
					<a href="<?php echo esc_url( site_url( '/wishlist/' ) ); ?>" class="fe-action-icon" aria-label="Wishlist">
						<i class="far fa-heart"></i>
						<span class="fe-badge-count fe-wishlist-count">0</span>
					</a>

					<!-- Cart -->
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="fe-action-icon" aria-label="Cart">
						<i class="fas fa-shopping-bag"></i>
						<span class="fe-badge-count fe-cart-count">
							<?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
						</span>
					</a>
				</div>

			</div>
		</div>
	</header>
