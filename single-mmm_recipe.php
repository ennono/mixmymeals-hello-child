<?php
/**
 * Template for displaying a single Recipe (mmm_recipe)
 * „Stopf nicht!"-Redesign: Hero aus dem Plugin, Section-Heads.
 *
 * @package Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" <?php astra_primary_class(); ?>>

	<?php while ( have_posts() ) :
		the_post(); ?>

			<?php
			// Passwortgeschützt: nur das Formular (mmm_render_locked_single()).
			if ( mmm_render_locked_single() ) {
				continue;
			}
			?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-recipe' ); ?>>

			<div class="mmm-plan-page">

				<?php mmm_render_recipe_hero(); ?>

				<?php astra_primary_content_top(); ?>

				<div class="entry-content mmm-single-recipe">

					<?php
					// Welche Abschnitte es gibt und in welcher Reihenfolge sie stehen:
					// includes/single-sections.php, sortierbar unter Design →
					// Theme-Einstellungen.
					mmm_render_single_sections( 'recipe' );
					?>

				</div>

			</div>

		</article>

	<?php endwhile; ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
