<?php
/**
 * Template for displaying a single Meal (mmm_meal)
 * „Stopf nicht!"-Redesign: Hero aus dem Plugin, Section-Heads.
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

	<?php while ( have_posts() ) :
		the_post(); ?>

			<?php
			// Passwortgeschützt: nur das Formular (mmm_render_locked_single()).
			if ( mmm_render_locked_single() ) {
				continue;
			}
			?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-meal' ); ?>>

			<div class="mmm-plan-page">

				<?php echo do_shortcode( '[mmm_meal_hero]' ); ?>

				<div class="entry-content mmm-single-meal">

					<?php
					// Welche Abschnitte es gibt und in welcher Reihenfolge sie stehen:
					// includes/single-sections.php, sortierbar unter Design →
					// Theme-Einstellungen.
					mmm_render_single_sections( 'meal' );
					?>

				</div>

			</div>

		</article>

	<?php endwhile; ?>

	<?php mmm_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
