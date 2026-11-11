<?php
/**
 * Template for the ingredient archive (mmm_ingredient).
 * Mirrors Astra's archive.php (so the hero header is identical to the recipe
 * archive) but replaces the default post loop with a category-grouped index
 * of all ingredients ([mmm_ingredients_index]).
 *
 * @package Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<?php if ( astra_page_layout() === 'left-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<?php astra_archive_header(); ?>

		<?php echo do_shortcode( '[mmm_ingredients_index]' ); ?>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

<?php get_footer(); ?>
