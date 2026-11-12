<?php
/**
 * Eine gewöhnliche Seite (Impressum, Datenschutz, …): Titel und Inhalt im
 * Container des Themes.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<div id="primary" <?php mmm_primary_class(); ?>>
		<?php mmm_primary_content_top(); ?>
		<?php mmm_content_page_loop(); ?>
		<?php mmm_primary_content_bottom(); ?>
	</div><!-- #primary -->

<?php
get_footer();
