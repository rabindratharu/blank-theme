<?php
/**
 * Title: Short heading and paragraph and image on the left
 * Slug: blank-theme/banner-intro-image
 * Categories: banner, featured
 * Description: A Intro pattern with Short heading, paragraph and image on the left.
 *
 * @package Blank-Theme
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"56%"} -->
		<div class="wp-block-column" style="flex-basis:56%">
			<!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full"} -->
			<figure class="wp-block-image size-full">
				<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/build/images/botany-flowers.webp" alt="<?php echo esc_attr_x( 'Picture of a flower', 'Alt text for intro picture.', 'blank-theme' ); ?>" style="aspect-ratio:1;object-fit:cover"/>
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'New arrivals', 'Heading for banner pattern.', 'blank-theme' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'Like flowers that bloom in unexpected places, every story unfolds with beauty and resilience, revealing hidden wonders.', 'Sample description for banner with flower.', 'blank-theme' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button -->
				<div class="wp-block-button">
					<a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'Learn More', 'Button text of intro section.', 'blank-theme' ); ?></a>
				</div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
