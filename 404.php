<?php
/**
 * Template for 404 pages (nothing found at this address).
 *
 * The shell mirrors Astra's 404.php — same wrappers, same sidebar handling, so
 * the Customizer's layout settings keep working. Only the content is ours:
 * astra_404_content_template() would print the parent theme's "page not found"
 * plus a search form, and a blog search form is not what somebody who landed
 * here needs.
 *
 * Instead the page does what the rest of the site does: it offers something to
 * browse. A dead link is a bad moment to explain HTTP status codes and a good
 * moment to show what is cooking — the newest plans, the recipes we like, the
 * recipes that just arrived. Every row renders '' when it has nothing to show,
 * so a fresh installation does not end up with a page of empty boxes.
 *
 * @package MixMyMeals Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<?php if ( astra_page_layout() === 'left-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<section class="error-404 not-found mmm-plan-page mmm-404">

			<header class="mmm-404__head">

				<h1 class="mmm-404__title">
					<?php esc_html_e( 'Oops, this page is no longer on the plan.', 'mixmymeals-astra-child' ); ?>
				</h1>

				<p class="mmm-404__lead">
					<?php esc_html_e( 'The link is probably old or has a typo in it. Nothing is lost — here is what is cooking right now.', 'mixmymeals-astra-child' ); ?>
				</p>

				<div class="mmm-404__actions">
					<a class="mmm-btn mmm-btn--primary" href="<?php echo esc_url( get_post_type_archive_link( 'mmm_meal_plan' ) ); ?>">
						<?php esc_html_e( 'All meal plans', 'mixmymeals-astra-child' ); ?>
					</a>
					<a class="mmm-btn mmm-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'mmm_recipe' ) ); ?>">
						<?php esc_html_e( 'All recipes', 'mixmymeals-astra-child' ); ?>
					</a>
				</div>

			</header>

			<?php
			/*
			 * Plans first: a plan answers the question somebody came with ("what
			 * do I cook this week"), a single recipe only answers a part of it.
			 */
			if ( shortcode_exists( 'mmm_latest_meal_plans' ) ) {
				echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the shortcode.
					sprintf(
						'[mmm_latest_meal_plans count="8" title="%s"]',
						esc_attr__( 'Fresh meal plans', 'mixmymeals-astra-child' )
					)
				);
			}

			/*
			 * Two recipe rows, with the wording of the archive's browse card —
			 * mmm_recipe_browse_views() is the one place that names these views,
			 * so the 404 page cannot drift away from the archive it points at.
			 */
			$mmm_404_views = function_exists( 'mmm_recipe_browse_views' ) ? mmm_recipe_browse_views() : array();

			foreach ( array( 'fav', 'new' ) as $mmm_404_view ) {

				if ( ! isset( $mmm_404_views[ $mmm_404_view ] ) ) {
					continue;
				}

				list( $mmm_404_shortcode, $mmm_404_title, $mmm_404_note ) = $mmm_404_views[ $mmm_404_view ];

				if ( ! shortcode_exists( $mmm_404_shortcode ) ) {
					continue;
				}

				echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the shortcode.
					sprintf(
						'[%s title="%s" note="%s"]',
						$mmm_404_shortcode,
						esc_attr( $mmm_404_title ),
						esc_attr( $mmm_404_note )
					)
				);
			}

			// Und zuletzt der Weg über die Kategorien, für alle, die etwas
			// Bestimmtes suchen statt sich treiben zu lassen.
			if ( function_exists( 'mmm_render_recipe_category_terms_card' ) ) {
				mmm_render_recipe_category_terms_card();
			}
			?>

		</section><!-- .error-404 -->

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

<?php get_footer(); ?>
