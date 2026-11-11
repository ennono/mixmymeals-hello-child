<?php
/**
 * Template for displaying a single ingredient (mmm_ingredient).
 * Mirrors the recipe single: plugin hero + section heads.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-ingredient' ); ?>>

			<div class="mmm-plan-page">

				<?php echo do_shortcode( '[mmm_ingredient_hero]' ); ?>

				<?php astra_primary_content_top(); ?>

				<div class="entry-content mmm-single-ingredient">

					<?php
					$mmm_ingredient_nutrition = do_shortcode( '[mmm_nutrition_table_for_a_single_ingredient]' );
					if ( '' !== trim( $mmm_ingredient_nutrition ) ) :
						?>
						<section class="mmm-recipe-section">
							<div class="mmm-section-head">
								<h2><?php esc_html_e( 'Nutrition Details', 'mixmymeals-astra-child' ); ?></h2>
								<span class="mmm-section-head__line"></span>
							</div>
							<?php echo $mmm_ingredient_nutrition; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

					<?php
					$mmm_ingredient_products = do_shortcode( '[mmm_ingredient_products]' );
					if ( '' !== trim( $mmm_ingredient_products ) ) :
						?>
						<section class="mmm-recipe-section">
							<div class="mmm-section-head">
								<h2><?php echo esc_html__( 'Products', 'mixmymeals' ); ?></h2>
								<span class="mmm-section-head__line"></span>
							</div>
							<?php echo $mmm_ingredient_products; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

					<?php
					$mmm_ingredient_body = trim( get_the_content() );
					if ( '' !== $mmm_ingredient_body ) :
						?>
						<section class="mmm-recipe-content mmm-ingredient-content">
							<?php the_content(); ?>
						</section>
					<?php endif; ?>

					<?php
					$mmm_ingredient_recipes = do_shortcode( '[mmm_recipes_for_a_single_ingredient]' );
					if ( '' !== trim( $mmm_ingredient_recipes ) ) :
						?>
						<section class="mmm-recipe-section">
							<div class="mmm-section-head">
								<h2><?php echo esc_html__( 'Used in these recipes', 'mixmymeals-astra-child' ); ?></h2>
								<span class="mmm-section-head__line"></span>
							</div>
							<?php echo $mmm_ingredient_recipes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

					<?php
					/*
					 * Der Haftungsausschluss, in derselben Hinweis-Section wie
					 * auf der Rezeptseite (mmm_recipe_section_note()) und mit
					 * demselben Heading, damit beide Seiten gleich lesen.
					 *
					 * Der Shortcode gibt '' zurück, solange die Einstellung
					 * „Auch auf Zutatenseiten ausgeben" aus ist oder kein
					 * Disclaimer hinterlegt ist — geprüft wird er deshalb hier
					 * und nicht die Einstellung selbst: ein Heading über einem
					 * leeren Kasten ist schlimmer als keiner.
					 */
					$mmm_ingredient_note = do_shortcode( '[mmm_disclaimer_for_a_single_ingredient]' );
					if ( '' !== trim( $mmm_ingredient_note ) ) :
						?>
						<section class="mmm-meal-section">
							<?php mmm_single_section_head( __( 'Note', 'mixmymeals-astra-child' ) ); ?>
							<?php echo $mmm_ingredient_note; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

				</div>

			</div>

		</article>

	<?php endwhile; ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
