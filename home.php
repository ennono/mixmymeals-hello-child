<?php
/**
 * Die Beitragsseite — der Blog selbst.
 *
 * Eigene Datei, weil WordPress die Beitragsseite nicht über archive.php
 * ausliefert (home.php → index.php). Beide zeigen dasselbe und holen es aus
 * derselben Funktion; hier steht nur die Hülle.
 *
 * @package MixMyMeals Astra Child
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" <?php astra_primary_class(); ?>>

	<?php astra_primary_content_top(); ?>

	<?php mmm_blog_render_archive(); ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
