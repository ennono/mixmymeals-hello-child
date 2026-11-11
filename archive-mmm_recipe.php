<?php
/**
 * Template for the recipe post-type archive (mmm_recipe).
 *
 * A 1:1 mirror of Astra's archive.php (classic loop WITH pagination) whose
 * content is not written out block by block: the blocks are named in
 * includes/archive-sections.php and rendered in the order stored under
 * Design → Theme-Einstellungen. Archive description and search form sit in the
 * archive header and are rendered from there — see that file.
 *
 * Applies to the "all recipes" post-type archive only. Recipe category and tag
 * taxonomy archives keep Astra's normal template (parent archive.php).
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

		<?php mmm_render_archive_sections( 'recipe_archive' ); ?>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

<?php get_footer(); ?>
