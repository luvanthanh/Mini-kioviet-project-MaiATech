<?php
/**
 * The Template for displaying product archives (Shop / Category) - WoodMart Furniture 2 Style
 *
 * @package FurnitureEcommerse
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$filter_data = FE_Ajax_Filter::get_filter_data();
$total_products = wc_get_loop_prop( 'total' );
?>

<!-- 1. Shop Page Header & Clean Breadcrumbs Bar -->
<div class="fe-shop-header-bar">
	<div class="fe-container">
		<div class="fe-shop-breadcrumb-wrap">
			<nav class="fe-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
				<?php if ( is_product_category() ) : 
					$term = get_queried_object(); ?>
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Shop</a>
					<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
					<span class="fe-breadcrumb__current"><?php echo esc_html( $term->name ); ?></span>
				<?php else : ?>
					<span class="fe-breadcrumb__current">Shop</span>
				<?php endif; ?>
			</nav>
		</div>

		<div class="fe-shop-title-wrap">
			<h1 class="fe-shop-hero__title">
				<?php woocommerce_page_title(); ?>
			</h1>
			<?php
			if ( is_product_category() ) {
				$term = get_queried_object();
				if ( $term && ! empty( $term->description ) ) {
					echo '<p class="fe-shop-hero__desc">' . esc_html( wp_strip_all_tags( $term->description ) ) . '</p>';
				} else {
					echo '<p class="fe-shop-hero__desc">Explore our curated collection of Scandinavian design crafted from certified natural oak and tactile upholstery.</p>';
				}
			} else {
				echo '<p class="fe-shop-hero__desc">Discover timeless Scandinavian furniture, lighting, and tactile homeware designed for mindful everyday living.</p>';
			}
			?>
		</div>
	</div>
</div>

<!-- 2. Shop Main Content Area -->
<div class="fe-container fe-shop-container">
	<div class="fe-shop-layout">
		
		<!-- Sidebar Filters (WoodMart Style) -->
		<aside class="fe-shop-sidebar" id="fe-shop-sidebar">
			<div class="fe-sidebar-header">
				<h3 class="fe-sidebar-title"><i class="fas fa-sliders"></i> Filter Products</h3>
				<button class="fe-sidebar-close" id="fe-sidebar-close" aria-label="Close filters">
					<i class="fas fa-times"></i>
				</button>
			</div>

			<form class="fe-filter-form" id="fe-filter-form">
				
				<!-- Categories Widget -->
				<div class="fe-filter-widget">
					<h4 class="fe-filter-widget__title">Categories</h4>
					<ul class="fe-filter-list">
						<?php foreach ( $filter_data['categories'] as $cat ) : ?>
							<li class="fe-filter-item">
								<label class="fe-checkbox-label">
									<input type="checkbox" name="category[]" value="<?php echo esc_attr( $cat->slug ); ?>"
										<?php echo ( is_product_category( $cat->slug ) ) ? 'checked' : ''; ?>>
									<span class="fe-checkbox-custom"></span>
									<span class="fe-filter-name"><?php echo esc_html( $cat->name ); ?></span>
									<span class="fe-filter-count"><?php echo esc_html( $cat->count ); ?></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<!-- Brands Widget -->
				<?php if ( ! empty( $filter_data['brands'] ) ) : ?>
					<div class="fe-filter-widget">
						<h4 class="fe-filter-widget__title">Brand</h4>
						<ul class="fe-filter-list">
							<?php foreach ( $filter_data['brands'] as $brand ) : ?>
								<li class="fe-filter-item">
									<label class="fe-checkbox-label">
										<input type="checkbox" name="brand[]" value="<?php echo esc_attr( $brand->slug ); ?>">
										<span class="fe-checkbox-custom"></span>
										<span class="fe-filter-name"><?php echo esc_html( $brand->name ); ?></span>
										<span class="fe-filter-count"><?php echo esc_html( $brand->count ); ?></span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<!-- Dual-Handle Price Slider Widget (WoodMart Rubric Style) -->
				<div class="fe-filter-widget fe-filter-price-widget">
					<h4 class="fe-filter-widget__title">Price</h4>
					<div class="fe-price-slider-wrap">
						<div class="fe-price-slider-track-wrap">
							<div class="fe-price-slider-track">
								<div class="fe-price-slider-bar" id="fe-price-slider-bar"></div>
							</div>
							<input type="range" class="fe-range-slider fe-range-min" id="fe-range-min"
								min="<?php echo esc_attr( $filter_data['min_price'] ); ?>"
								max="<?php echo esc_attr( $filter_data['max_price'] ); ?>"
								value="<?php echo esc_attr( $filter_data['min_price'] ); ?>"
								step="5">
							<input type="range" class="fe-range-slider fe-range-max" id="fe-range-max"
								min="<?php echo esc_attr( $filter_data['min_price'] ); ?>"
								max="<?php echo esc_attr( $filter_data['max_price'] ); ?>"
								value="<?php echo esc_attr( $filter_data['max_price'] ); ?>"
								step="5">
						</div>
						<div class="fe-price-slider-label">
							<span class="fe-price-label-text">Price:</span>
							<strong class="fe-price-amount" id="fe-price-amount">
								$<?php echo esc_html( $filter_data['min_price'] ); ?> — $<?php echo esc_html( $filter_data['max_price'] ); ?>
							</strong>
						</div>
						<input type="hidden" name="min_price" id="fe-min-price-input" value="<?php echo esc_attr( $filter_data['min_price'] ); ?>">
						<input type="hidden" name="max_price" id="fe-max-price-input" value="<?php echo esc_attr( $filter_data['max_price'] ); ?>">
					</div>
				</div>

				<!-- Colors Widget -->
				<?php if ( ! empty( $filter_data['colors'] ) ) : ?>
					<div class="fe-filter-widget">
						<h4 class="fe-filter-widget__title">Color</h4>
						<div class="fe-swatches-grid">
							<?php
							$color_map = array(
								'black' => '#242424',
								'white' => '#ffffff',
								'brown' => '#8B4513',
								'gray'  => '#9e9e9e',
								'beige' => '#d4c5a9',
								'navy'  => '#1a1a2e',
								'terracotta' => '#f59a57',
								'oak'   => '#c6a664',
							);
							foreach ( $filter_data['colors'] as $color ) :
								$bg = $color_map[ strtolower( $color->name ) ] ?? '#cccccc';
								?>
								<label class="fe-swatch-label" title="<?php echo esc_attr( $color->name ); ?>">
									<input type="checkbox" name="color[]" value="<?php echo esc_attr( $color->slug ); ?>" style="display:none;">
									<span class="fe-swatch-circle" style="background-color: <?php echo esc_attr( $bg ); ?>;"></span>
									<span class="fe-swatch-tooltip"><?php echo esc_html( $color->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Sizes Widget -->
				<?php if ( ! empty( $filter_data['sizes'] ) ) : ?>
					<div class="fe-filter-widget">
						<h4 class="fe-filter-widget__title">Size</h4>
						<div class="fe-size-pills">
							<?php foreach ( $filter_data['sizes'] as $size ) : ?>
								<label class="fe-size-pill-label">
									<input type="checkbox" name="size[]" value="<?php echo esc_attr( $size->slug ); ?>" style="display:none;">
									<span class="fe-size-pill"><?php echo esc_html( $size->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Availability Widget -->
				<div class="fe-filter-widget">
					<h4 class="fe-filter-widget__title">Availability</h4>
					<ul class="fe-filter-list">
						<li class="fe-filter-item">
							<label class="fe-checkbox-label">
								<input type="checkbox" name="in_stock" value="1">
								<span class="fe-checkbox-custom"></span>
								<span class="fe-filter-name">In Stock Only</span>
							</label>
						</li>
						<li class="fe-filter-item">
							<label class="fe-checkbox-label">
								<input type="checkbox" name="on_sale" value="1">
								<span class="fe-checkbox-custom"></span>
								<span class="fe-filter-name">On Sale</span>
							</label>
						</li>
					</ul>
				</div>

				<!-- Reset Filters -->
				<div class="fe-filter-reset">
					<button type="reset" class="fe-btn-reset-filters" onclick="$('.fe-filter-form')[0].reset(); $('.fe-filter-form').trigger('change');">
						<i class="fas fa-rotate-left"></i> Reset All Filters
					</button>
				</div>
			</form>
		</aside>

		<!-- Products Section -->
		<div class="fe-shop-products-wrap">
			
			<!-- WoodMart Shop Toolbar -->
			<div class="fe-shop-toolbar">
				<div class="fe-shop-toolbar__left">
					<button class="fe-mobile-filter-btn" id="fe-mobile-filter-btn">
						<i class="fas fa-sliders"></i> Filters
					</button>
					<p class="fe-shop-result-count">
						Showing all <strong><?php echo esc_html( $total_products ); ?></strong> results
					</p>
				</div>

				<div class="fe-shop-toolbar__right">
					<div class="fe-shop-sorting">
						<label for="fe-orderby-select" class="screen-reader-text">Sort by</label>
						<select id="fe-orderby-select" name="orderby" class="fe-orderby-select" onchange="$('.fe-filter-form').find('input[name=orderby]').remove(); $('.fe-filter-form').append('<input type=\'hidden\' name=\'orderby\' value=\''+this.value+'\'>').trigger('change');">
							<option value="date">Sort by latest</option>
							<option value="popularity">Sort by popularity</option>
							<option value="rating">Sort by average rating</option>
							<option value="price-asc">Sort by price: low to high</option>
							<option value="price-desc">Sort by price: high to low</option>
						</select>
					</div>
				</div>
			</div>

			<!-- Product Grid -->
			<div class="fe-product-grid" id="fe-shop-product-grid">
				<?php
				if ( woocommerce_product_loop() ) {
					while ( have_posts() ) {
						the_post();
						echo fe_render_product_card();
					}
				} else {
					echo '<div class="fe-no-products"><i class="fas fa-box-open"></i><p>No products found matching your selection.</p></div>';
				}
				?>
			</div>
			
			<!-- Pagination -->
			<div class="fe-pagination">
				<?php
				echo paginate_links( array(
					'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
					'format'    => '',
					'add_args'  => false,
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => wc_get_loop_prop( 'total_pages' ),
					'prev_text' => '<i class="fas fa-chevron-left"></i>',
					'next_text' => '<i class="fas fa-chevron-right"></i>',
					'type'      => 'list',
					'end_size'  => 3,
					'mid_size'  => 3,
				) );
				?>
			</div>

		</div>
	</div>
</div>

<?php
get_footer( 'shop' );
