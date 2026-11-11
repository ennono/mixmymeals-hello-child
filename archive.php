<?php
/**
 * Archiv der Blogbeiträge: Kategorie, Schlagwort, Datum, Autor.
 *
 * **Nicht für die eigenen Inhaltstypen.** Die Template-Hierarchie schickt auch
 * Rezept-Kategorien und -Schlagwörter hier vorbei (taxonomy.php → archive.php),
 * und die sollen bei Astras Archiv bleiben: ihr Kopf, die Filterzeile und die
 * Karten hängen an dessen Aufbau. Darum die Weiche in der ersten Zeile — ohne
 * sie wäre dieses Theme der stille Grund, warum die Rezeptfilter eines Tages
 * fehlen.
 *
 * Die Beitragsseite selbst (is_home) liegt in home.php, die Darstellung für
 * beide in includes/blog.php.
 *
 * @package MixMyMeals Astra Child
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! mmm_blog_is_post_archive() ) {
	/*
	 * Astras eigene Vorlage, nicht locate_template(): das fände wieder diese
	 * Datei und liefe im Kreis.
	 */
	require get_template_directory() . '/archive.php';
	return;
}

get_header(); ?>

<div id="primary" <?php astra_primary_class(); ?>>

	<?php astra_primary_content_top(); ?>

	<?php mmm_blog_render_archive(); ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
