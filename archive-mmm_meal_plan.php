<?php
/**
 * Template for the meal plan post-type archive (mmm_meal_plan).
 *
 * A 1:1 mirror of Astra's archive.php, like the recipe archive: it exists so
 * the blocks of this archive can be reordered. They are named in
 * includes/archive-sections.php and rendered in the order stored under
 * Design → Theme-Einstellungen. Archive description, search form and category
 * filter sit in the archive header and are rendered from there — see that file.
 *
 * Applies to the post-type archive only. Meal plan category and tag archives
 * keep Astra's normal template (parent archive.php).
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

		<?php mmm_render_archive_sections( 'plan_archive' ); ?>

		<?php mmm_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php get_footer(); ?>
