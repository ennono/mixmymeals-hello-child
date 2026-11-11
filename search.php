<?php
/**
 * Die Ergebnisseite der Suche.
 *
 * Hülle wie bei Astras search.php, Inhalt eigener: statt einer Liste, in der
 * ein Plan neben einer Zutat steht, stehen die Treffer in Gruppen —
 * Rezepte, Pläne, Zutaten, Geräte, in dieser Reihenfolge. Welche Treffer eine
 * Gruppe hat und wie sie geordnet sind, entscheidet includes/search.php.
 *
 * Je Gruppe die ersten sechs; wer mehr will, geht über den Link ins Archiv,
 * wo Filter und Blätterlinks warten. Leere Gruppen erscheinen nicht.
 *
 * @package MixMyMeals Astra Child
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Wie viele Treffer eine Gruppe auf dieser Seite zeigt. */
const MMM_SEARCH_GROUP_LIMIT = 6;

get_header(); ?>

<?php if ( astra_page_layout() === 'left-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<section class="mmm-plan-page mmm-search">

			<?php
			$mmm_term = mmm_search_term();

			$mmm_gruppen = array();
			$mmm_gesamt  = 0;

			foreach ( mmm_search_groups() as $mmm_key => $mmm_group ) {

				$mmm_ids = mmm_search_ids( $mmm_key, $mmm_term );

				if ( empty( $mmm_ids ) ) {
					continue;
				}

				$mmm_gruppen[ $mmm_key ] = $mmm_ids;
				$mmm_gesamt             += count( $mmm_ids );
			}
			?>

			<header class="mmm-search__head">

				<h1 class="mmm-search__title">
					<?php
					printf(
						/* translators: %s: the search term. */
						esc_html__( 'Search results for “%s”', 'mixmymeals-astra-child' ),
						esc_html( $mmm_term )
					);
					?>
				</h1>

				<?php if ( $mmm_gesamt > 0 ) : ?>
					<p class="mmm-search__count">
						<?php
						printf(
							esc_html(
								/* translators: %s: number of results. */
								_n( '%s result', '%s results', $mmm_gesamt, 'mixmymeals-astra-child' )
							),
							esc_html( number_format_i18n( $mmm_gesamt ) )
						);
						?>
					</p>
				<?php endif; ?>

				<?php get_search_form(); ?>

			</header>

			<?php if ( empty( $mmm_gruppen ) ) : ?>

				<p class="mmm-search__empty">
					<?php esc_html_e( 'Nothing matched that. Maybe a different word — or have a look at what is cooking:', 'mixmymeals-astra-child' ); ?>
				</p>

				<?php
				/*
				 * Dieselben Stöber-Zeilen wie auf der 404-Seite: eine leere
				 * Suche ist derselbe Moment — jemand steht vor nichts und
				 * braucht einen Weg weiter.
				 */
				if ( shortcode_exists( 'mmm_latest_meal_plans' ) ) {
					echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the shortcode.
						sprintf(
							'[mmm_latest_meal_plans count="8" title="%s"]',
							esc_attr__( 'Fresh meal plans', 'mixmymeals-astra-child' )
						)
					);
				}

				$mmm_views = function_exists( 'mmm_recipe_browse_views' ) ? mmm_recipe_browse_views() : array();

				if ( isset( $mmm_views['new'] ) && shortcode_exists( $mmm_views['new'][0] ) ) {
					echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the shortcode.
						sprintf(
							'[%s title="%s" note="%s"]',
							$mmm_views['new'][0],
							esc_attr( $mmm_views['new'][1] ),
							esc_attr( $mmm_views['new'][2] )
						)
					);
				}
				?>

			<?php else : ?>

				<?php foreach ( $mmm_gruppen as $mmm_key => $mmm_ids ) : ?>

					<?php
					$mmm_group  = mmm_search_groups()[ $mmm_key ];
					$mmm_anzahl = count( $mmm_ids );
					$mmm_zeigen = array_slice( $mmm_ids, 0, MMM_SEARCH_GROUP_LIMIT );
					$mmm_link   = mmm_search_group_link( $mmm_key, $mmm_term );
					?>

					<section class="mmm-search-group mmm-search-group--<?php echo esc_attr( $mmm_key ); ?>">

						<div class="mmm-section-head">
							<h2>
								<?php echo esc_html( $mmm_group['label'] ); ?>
								<span class="mmm-search-group__count"><?php echo esc_html( number_format_i18n( $mmm_anzahl ) ); ?></span>
							</h2>
							<span class="mmm-section-head__line"></span>
						</div>

						<ul class="mmm-search-hits">
							<?php foreach ( $mmm_zeigen as $mmm_id ) : ?>
								<li class="mmm-search-hit">
									<a class="mmm-search-hit__link" href="<?php echo esc_url( (string) get_permalink( $mmm_id ) ); ?>">
										<?php if ( has_post_thumbnail( $mmm_id ) ) : ?>
											<span class="mmm-search-hit__image">
												<?php echo get_the_post_thumbnail( $mmm_id, 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</span>
										<?php endif; ?>
										<span class="mmm-search-hit__title"><?php echo esc_html( get_the_title( $mmm_id ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>

						<?php if ( '' !== $mmm_link && $mmm_anzahl > MMM_SEARCH_GROUP_LIMIT ) : ?>
							<p class="mmm-search-group__more">
								<a href="<?php echo esc_url( $mmm_link ); ?>">
									<?php
									printf(
										/* translators: 1: number of results, 2: group name, e.g. “Recipes”. */
										esc_html__( 'Show all %1$s hits in %2$s', 'mixmymeals-astra-child' ),
										esc_html( number_format_i18n( $mmm_anzahl ) ),
										esc_html( $mmm_group['label'] )
									);
									?>
								</a>
							</p>
						<?php endif; ?>

					</section>

				<?php endforeach; ?>

			<?php endif; ?>

		</section>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>
	<?php get_sidebar(); ?>
<?php } ?>

<?php get_footer(); ?>
