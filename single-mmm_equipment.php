<?php
/**
 * Template for displaying a single piece of equipment (mmm_equipment).
 * Mirrors the ingredient single: plugin hero + section heads.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-equipment' ); ?>>

			<div class="mmm-plan-page">

				<?php echo do_shortcode( '[mmm_equipment_hero]' ); ?>

				<?php astra_primary_content_top(); ?>

				<div class="entry-content mmm-single-equipment">

					<?php
					$mmm_equipment_body = trim( get_the_content() );
					if ( '' !== $mmm_equipment_body ) :
						?>
						<section class="mmm-recipe-content mmm-equipment-content">
							<?php the_content(); ?>
						</section>
					<?php endif; ?>

					<?php
					// Kaufhinweise nach der Beschreibung: erst verstehen, dann kaufen.
					$mmm_equipment_shops = do_shortcode( '[mmm_equipment_affiliate_links]' );
					if ( '' !== trim( $mmm_equipment_shops ) ) :
						?>
						<section class="mmm-recipe-section">
							<div class="mmm-section-head">
								<h2><?php echo esc_html__( 'Where to buy', 'mixmymeals' ); ?></h2>
								<span class="mmm-section-head__line"></span>
							</div>
							<?php echo $mmm_equipment_shops; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

					<?php
					$mmm_equipment_recipes = do_shortcode( '[mmm_recipes_for_a_single_equipment]' );
					if ( '' !== trim( $mmm_equipment_recipes ) ) :
						?>
						<section class="mmm-recipe-section">
							<div class="mmm-section-head">
								<h2><?php echo esc_html__( 'Used in these recipes', 'mixmymeals' ); ?></h2>
								<span class="mmm-section-head__line"></span>
							</div>
							<?php echo $mmm_equipment_recipes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

				</div>

			</div>

		</article>

	<?php endwhile; ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
