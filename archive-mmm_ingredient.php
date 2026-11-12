<?php
/**
 * Template for the ingredient archive (mmm_ingredient).
 * Mirrors Astra's archive.php (so the hero header is identical to the recipe
 * archive) but replaces the default post loop with a category-grouped index
 * of all ingredients ([mmm_ingredients_index]).
 *
 * @package MixMyMeals Hello Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<div id="primary" <?php mmm_primary_class(); ?>>

		<?php mmm_primary_content_top(); ?>

		<?php mmm_archive_header(); ?>

		<?php echo do_shortcode( '[mmm_ingredients_index]' ); ?>

		<?php mmm_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php get_footer(); ?>
