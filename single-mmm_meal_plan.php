<?php
/**
 * Template for displaying a single Meal Plan (CPT: mmm_meal_plan)
 * „Stopf nicht!"-Redesign: Hero + Tages-Rail + Nährwerte aus dem Plugin.
 *
 * @package MixMyMeals
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" <?php mmm_primary_class(); ?>>

	<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			// Passwortgeschützt: nur das Formular (mmm_render_locked_single()).
			if ( mmm_render_locked_single() ) {
				continue;
			}
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-meal-plan-single' ); ?>>

				<div class="mmm-plan-page">

					<?php echo do_shortcode( '[mmm_meal_plan_hero]' ); ?>

					<div class="entry-content">

						<?php
						// Welche Abschnitte es gibt und in welcher Reihenfolge sie stehen:
						// includes/single-sections.php, sortierbar unter Design →
						// Theme-Einstellungen.
						mmm_render_single_sections( 'plan' );
						?>

					</div>

				</div>

			</article>

		<?php endwhile; ?>

	</main>

</div><!-- #primary -->

<?php get_footer(); ?>
