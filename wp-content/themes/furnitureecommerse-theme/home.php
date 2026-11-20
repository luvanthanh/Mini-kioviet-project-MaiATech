<?php
/**
 * The template for displaying the Blog / Journal archive page.
 * Strictly complies with Requirement G:
 * - Featured Post
 * - Latest Posts
 * - Categories
 * - Search
 * - Author, Date, Featured Image, Excerpt
 *
 * @package FurnitureEcommerse
 */

get_header();

// Setup pagination
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

// Blog query arguments
$blog_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

// If category filter
if ( is_category() ) {
	$current_cat = get_queried_object();
	if ( $current_cat ) {
		$blog_args['cat'] = $current_cat->term_id;
	}
} elseif ( ! empty( $_GET['category'] ) ) {
	$cat_slug = sanitize_text_field( $_GET['category'] );
	$blog_args['category_name'] = $cat_slug;
}

// If search query
if ( is_search() || ! empty( $_GET['s'] ) ) {
	$blog_args['s'] = get_search_query() ? get_search_query() : sanitize_text_field( $_GET['s'] );
}

$blog_posts_query = new WP_Query( $blog_args );
?>

<main id="primary" class="site-main fe-blog-archive-page">
	
	<!-- 1. Blog Hero Header Bar (Consistent with Shop Header Bar) -->
	<div class="fe-shop-header-bar">
		<div class="fe-container">
			<div class="fe-shop-breadcrumb-wrap">
				<nav class="fe-breadcrumb" aria-label="Breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
					<?php if ( is_category() ) : 
						$current_cat = get_queried_object(); ?>
						<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Journal</a>
						<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
						<span class="fe-breadcrumb__current"><?php echo esc_html( $current_cat->name ); ?></span>
					<?php else : ?>
						<span class="fe-breadcrumb__current">Journal & Editorial</span>
					<?php endif; ?>
				</nav>
			</div>

			<div class="fe-shop-title-wrap">
				<h1 class="fe-shop-hero__title">
					<?php
					if ( is_category() ) {
						single_cat_title( 'Category: ' );
					} elseif ( is_search() || ! empty( $_GET['s'] ) ) {
						echo 'Search Results for: &ldquo;' . esc_html( get_search_query() ?: $_GET['s'] ) . '&rdquo;';
					} else {
						echo 'Our Journal & Editorial';
					}
					?>
				</h1>
				<p class="fe-shop-hero__desc">Curated architectural inspiration, craftsmanship stories, and thoughtful guides for mindful Scandinavian living.</p>
				
				<!-- Blog Search Form -->
				<div class="fe-blog-search-bar" style="max-width: 520px; margin: 18px 0 0;">
					<form role="search" method="get" action="<?php echo esc_url( home_url( '/blog/' ) ); ?>" style="display:flex; background:#fff; border-radius:30px; padding:6px 12px; box-shadow:0 4px 15px rgba(0,0,0,0.06); border:1px solid var(--fe-color-border);">
						<input type="search" name="s" placeholder="Search journal articles..." value="<?php echo esc_attr( get_search_query() ?: ( $_GET['s'] ?? '' ) ); ?>" style="flex:1; border:none; outline:none; padding:8px 14px; font-size:14px; background:transparent;">
						<input type="hidden" name="post_type" value="post">
						<button type="submit" style="background:var(--fe-color-primary); color:#fff; border:none; border-radius:50%; width:38px; height:38px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
							<i class="fas fa-search"></i>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="fe-container" style="padding-top: 20px; padding-bottom: 80px;">
		
		<!-- 2. Category Filter Pills -->
		<div class="fe-blog-categories-bar" style="display:flex; justify-content:flex-start; gap:12px; margin-bottom:45px; flex-wrap:wrap;">
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="fe-tab-btn <?php echo ( ! is_category() && empty( $_GET['category'] ) ) ? 'active' : ''; ?>" style="text-decoration:none;">
				All Articles
			</a>
			<?php
			$blog_cats = get_categories( array( 'hide_empty' => true ) );
			$active_cat_id = is_category() ? get_queried_object_id() : 0;
			foreach ( $blog_cats as $bcat ) :
				$is_active = ( $active_cat_id === $bcat->term_id || ( ! empty( $_GET['category'] ) && $_GET['category'] === $bcat->slug ) );
				?>
				<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" class="fe-tab-btn <?php echo $is_active ? 'active' : ''; ?>" style="text-decoration:none;">
					<?php echo esc_html( $bcat->name ); ?> (<?php echo esc_html( $bcat->count ); ?>)
				</a>
			<?php endforeach; ?>
		</div>

		<?php
		// 3. Featured Post Spotlight Card (Only on Page 1 and when not searching)
		if ( 1 === (int) $paged && ! is_search() && empty( $_GET['s'] ) ) :
			$featured_query = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			if ( $featured_query->have_posts() ) :
				while ( $featured_query->have_posts() ) :
					$featured_query->the_post();
					$meta_img = get_post_meta( get_the_ID(), '_fe_post_banner_img', true );
					$feat_img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : ( $meta_img ?: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=85' );
					$cats = get_the_category();
					$cat_name = ! empty( $cats ) ? $cats[0]->name : 'Featured';
					?>
					<div class="fe-featured-post-card" style="display:flex; gap:40px; background:#fff; border:1px solid var(--fe-color-border); border-radius:12px; overflow:hidden; margin-bottom:60px; box-shadow:0 4px 20px rgba(0,0,0,0.04); align-items:center; flex-wrap:wrap;">
						<div style="flex:1.2; min-width:320px; aspect-ratio:16/9; overflow:hidden;">
							<a href="<?php the_permalink(); ?>" style="display:block; width:100%; height:100%;">
								<img src="<?php echo esc_url( $feat_img ); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.4s ease;">
							</a>
						</div>
						<div style="flex:1; min-width:300px; padding:35px 40px 35px 20px;">
							<span style="display:inline-block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:var(--fe-color-accent, #c97a47); margin-bottom:12px;">
								★ FEATURED STORY • <?php echo esc_html( $cat_name ); ?>
							</span>
							<h2 style="font-size: clamp(22px, 2.5vw, 30px); margin:0 0 15px; line-height:1.3;">
								<a href="<?php the_permalink(); ?>" style="color:var(--fe-color-primary, #0f172a); text-decoration:none;">
									<?php the_title(); ?>
								</a>
							</h2>
							<p style="color:var(--fe-color-text-light, #64748b); line-height:1.7; font-size:15px; margin-bottom:20px;">
								<?php echo wp_trim_words( get_the_excerpt(), 25, '...' ); ?>
							</p>
							<div style="display:flex; align-items:center; gap:20px; font-size:13px; color:var(--fe-color-text-muted); margin-bottom:25px;">
								<span><i class="far fa-user"></i> <?php the_author(); ?></span>
								<span><i class="far fa-calendar"></i> <?php echo get_the_date(); ?></span>
							</div>
							<a href="<?php the_permalink(); ?>" class="fe-btn fe-btn--primary">
								Read Full Story <i class="fas fa-arrow-right"></i>
							</a>
						</div>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
		endif;
		?>

		<!-- 4. Latest Posts Section -->
		<div class="fe-section-header" style="text-align:left; margin-bottom:30px;">
			<span class="fe-section-header__subtitle">ALL STORIES</span>
			<h2 class="fe-section-header__title">Latest Journal Posts</h2>
		</div>

		<div class="fe-blog-grid">
			<?php
			$blog_fallbacks = array(
				'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=800&q=80',
				'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
				'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
			);

			$b_idx = 0;
			if ( $blog_posts_query->have_posts() ) :
				while ( $blog_posts_query->have_posts() ) :
					$blog_posts_query->the_post();
					$meta_img = get_post_meta( get_the_ID(), '_fe_post_banner_img', true );
					$img_src  = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : ( $meta_img ?: $blog_fallbacks[ $b_idx % 3 ] );
					$cats = get_the_category();
					$cat_label = ! empty( $cats ) ? $cats[0]->name : 'Journal';
					$b_idx++;
					?>
					<article class="fe-blog-card">
						<a href="<?php the_permalink(); ?>" class="fe-blog-card__image-link">
							<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php the_title_attribute(); ?>">
							<span class="fe-blog-card__category"><?php echo esc_html( $cat_label ); ?></span>
						</a>
						<div class="fe-blog-card__body">
							<div class="fe-blog-card__meta">
								<span><i class="far fa-user"></i> <?php the_author(); ?></span>
								<span><i class="far fa-calendar"></i> <?php echo get_the_date(); ?></span>
							</div>
							<h3 class="fe-blog-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="fe-blog-card__excerpt">
								<?php echo wp_trim_words( get_the_excerpt(), 18, '...' ); ?>
							</p>
							<a href="<?php the_permalink(); ?>" class="fe-blog-card__more">
								Read Article <i class="fas fa-arrow-right"></i>
							</a>
						</div>
					</article>
				<?php
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px 0; color: var(--fe-color-text-muted);">No journal articles found.</p>';
			endif;
			?>
		</div>

		<!-- Pagination -->
		<?php if ( $blog_posts_query->max_num_pages > 1 ) : ?>
			<div class="fe-pagination" style="margin-top: 50px;">
				<?php
				echo paginate_links( array(
					'total'     => $blog_posts_query->max_num_pages,
					'current'   => max( 1, $paged ),
					'prev_text' => '<i class="fas fa-chevron-left"></i>',
					'next_text' => '<i class="fas fa-chevron-right"></i>',
					'type'      => 'list',
				) );
				?>
			</div>
		<?php endif; ?>

	</div>

</main>

<?php get_footer(); ?>
