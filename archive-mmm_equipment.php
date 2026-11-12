<?php
/**
 * Template for the equipment archive (mmm_equipment).
 * Mirrors the ingredient archive: Astra hero header plus an alphabetical
 * index of all equipment ([mmm_equipment_index]).
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

		<?php echo do_shortcode( '[mmm_equipment_index]' ); ?>

		<?php mmm_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php get_footer(); ?>
