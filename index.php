<?php
/**
 * Auffangnetz für alles, wofür es kein eigenes Template gibt: einzelne
 * Inhalte wie eine Seite, Listen als Archiv. Ohne diese Datei griffe die
 * index.php von Hello Elementor mit eigenem Markup.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

	<div id="primary" <?php mmm_primary_class(); ?>>
		<?php mmm_primary_content_top(); ?>
		<?php
		if ( is_singular() ) {
			mmm_content_page_loop();
		} else {
			if ( is_archive() ) {
				mmm_archive_header();
			}
			mmm_content_loop();
			mmm_pagination();
		}
		?>
		<?php mmm_primary_content_bottom(); ?>
	</div><!-- #primary -->

<?php
get_footer();
