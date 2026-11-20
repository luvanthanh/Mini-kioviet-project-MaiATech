<?php
/**
 * The template for displaying all single posts
 * Strictly complies with Requirement G:
 * - Featured image, Author, Date, Full content, and Related posts
 *
 * @package FurnitureEcommerse
 */

get_header(); ?>

<main id="primary" class="site-main fe-single-post-page">
	
	<!-- Breadcrumbs Bar -->
	<div class="fe-shop-header-bar">
		<div class="fe-container">
			<div class="fe-shop-breadcrumb-wrap">
				<nav class="fe-breadcrumb" aria-label="Breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
					<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Journal</a>
					<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>
					<span class="fe-breadcrumb__current"><?php the_title(); ?></span>
				</nav>
			</div>
		</div>
	</div>

	<div class="fe-container fe-single-post-container" style="max-width: 860px; margin: 40px auto 80px;">
		<?php
		while ( have_posts() ) :
			the_post();
			$meta_img = get_post_meta( get_the_ID(), '_fe_post_banner_img', true );
			$img_src  = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : ( $meta_img ?: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=85' );
			$categories = get_the_category();
			$cat_name = ! empty( $categories ) ? $categories[0]->name : 'Editorial';
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'fe-single-post-article' ); ?>>
				
				<!-- Post Header -->
				<header class="fe-post-header" style="margin-bottom: 30px;">
					<span class="fe-post-kicker" style="color: var(--fe-color-primary, #c97a47); font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: inline-block; margin-bottom: 12px;">
						<?php echo esc_html( $cat_name ); ?>
					</span>
					<h1 class="fe-post-title" style="font-size: clamp(26px, 3.5vw, 40px); font-weight: 700; line-height: 1.25; margin: 0 0 20px;">
						<?php the_title(); ?>
					</h1>
					<div class="fe-post-meta" style="display: flex; align-items: center; gap: 20px; font-size: 14px; color: var(--fe-color-text-muted); border-bottom: 1px solid var(--fe-color-border); padding-bottom: 20px;">
						<span><i class="far fa-user"></i> <?php the_author(); ?></span>
						<span><i class="far fa-calendar"></i> <?php echo get_the_date(); ?></span>
						<span><i class="far fa-clock"></i> 4 min read</span>
					</div>
				</header>

				<!-- Featured Image Banner -->
				<?php if ( $img_src ) : ?>
					<div class="fe-post-featured-media" style="margin-bottom: 40px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
						<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: auto; max-height: 520px; object-fit: cover; display: block;">
					</div>
				<?php endif; ?>

				<!-- Post Content -->
				<div class="fe-post-entry-content" style="font-size: 16px; line-height: 1.85; color: var(--fe-color-text, #334155); margin-bottom: 50px;">
					<?php the_content(); ?>
				</div>

				<!-- Author Box -->
				<div class="fe-post-author-box" style="display: flex; gap: 20px; align-items: center; background: #f8fafc; border: 1px solid var(--fe-color-border); border-radius: 12px; padding: 25px; margin-bottom: 60px;">
					<div style="width: 60px; height: 60px; border-radius: 50%; background: var(--fe-color-primary, #c97a47); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
						<?php echo strtoupper( substr( get_the_author(), 0, 1 ) ); ?>
					</div>
					<div>
						<h4 style="margin: 0 0 5px; font-size: 16px;"><?php the_author(); ?></h4>
						<p style="margin: 0; font-size: 14px; color: var(--fe-color-text-muted);">Editorial Writer & Interior Specialist at International Furniture Store.</p>
					</div>
				</div>

			</article>

			<!-- Related Articles Section (Requirement G) -->
			<section class="fe-related-articles-section" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--fe-color-border);">
				<div class="fe-section-header" style="text-align: left; margin-bottom: 30px;">
					<span class="fe-section-header__subtitle">CONTINUE READING</span>
					<h3 class="fe-section-header__title" style="font-size: 26px;">Related Articles</h3>
				</div>

				<div class="fe-blog-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
					<?php
					$related_args = array(
						'post_type'      => 'post',
						'posts_per_page' => 3,
						'post__not_in'   => array( get_the_ID() ),
						'post_status'    => 'publish',
					);
					if ( ! empty( $categories ) ) {
						$related_args['category__in'] = wp_list_pluck( $categories, 'term_id' );
					}
					$related_posts = new WP_Query( $related_args );
					if ( $related_posts->have_posts() ) :
						while ( $related_posts->have_posts() ) :
							$related_posts->the_post();
							$rel_meta_img = get_post_meta( get_the_ID(), '_fe_post_banner_img', true );
							$rel_img_src  = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) : ( $rel_meta_img ?: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=600&q=80' );
							$rel_cats     = get_the_category();
							$rel_cat_lbl  = ! empty( $rel_cats ) ? $rel_cats[0]->name : 'Journal';
							?>
							<article class="fe-blog-card">
								<a href="<?php the_permalink(); ?>" class="fe-blog-card__image-link">
									<img src="<?php echo esc_url( $rel_img_src ); ?>" alt="<?php the_title_attribute(); ?>">
									<span class="fe-blog-card__category"><?php echo esc_html( $rel_cat_lbl ); ?></span>
								</a>
								<div class="fe-blog-card__body">
									<div class="fe-blog-card__meta">
										<span><i class="far fa-calendar"></i> <?php echo get_the_date(); ?></span>
									</div>
									<h4 class="fe-blog-card__title" style="font-size: 16px;">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
									<p class="fe-blog-card__excerpt" style="font-size: 13px;">
										<?php echo wp_trim_words( get_the_excerpt(), 14, '...' ); ?>
									</p>
									<a href="<?php the_permalink(); ?>" class="fe-blog-card__more">
										Read Article <i class="fas fa-arrow-right"></i>
									</a>
								</div>
							</article>
						<?php
						endwhile;
						wp_reset_postdata();
					endif;
					?>
				</div>
			</section>

		<?php endwhile; ?>
	</div>

</main>

<?php get_footer(); ?>
