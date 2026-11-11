<?php
/**
 * Shortcodes des Themes.
 *
 * Hier stehen die, die Inhalte des Plugins zeigen — sie hängen deshalb am
 * class_exists() unten und verschwinden mit ihm. [mmm_blog_posts] (Beiträge
 * einer Kategorie als Reihe oder Raster) steht in includes/blog.php, bei dem
 * Renderer, den er umschließt: er kommt ohne das Plugin aus.
 */

if ( class_exists( 'mixmymeals' ) ) {

	add_shortcode(
		'mmm_home_masthead',
		function ( $atts = array() ) {

			$atts = shortcode_atts(
				array(
					'eyebrow'         => __( 'Inspiration', 'mixmymeals-astra-child' ),
					'headline_prefix' => __( 'Feel like', 'mixmymeals-astra-child' ),
					'headline_suffix' => '?',
					'primary_text'    => __( 'Go to recipe', 'mixmymeals-astra-child' ),

					// steuert wohin der Primary-Link führt: plan | meal | recipe
					'primary_target'  => 'plan',

					'secondary_text'  => __( 'All Recipes', 'mixmymeals-astra-child' ),
					'secondary_url'   => home_url( '' ),
					'image_size'      => 'large',
					'class'           => '',
				),
				$atts,
				'mmm_home_masthead'
			);

			// Plan, Gericht, Mahlzeit und Chipzeile liefert das Plugin — dieselbe
			// Zufallsauswahl wie Titel und Bild, und Chips im Wortlaut des Archivs
			// samt Werbekennzeichnung. Das Theme baut nur die Sektion darum.
			// Ab MixMyMeals 3.72.0; mit einem älteren Plugin bleibt die Sektion leer.
			$spot = is_callable( array( 'Mixmymeals_Meal_Plan', 'latest_plan_spotlight' ) )
			? Mixmymeals_Meal_Plan::latest_plan_spotlight()
			: array();

			if ( empty( $spot ) ) {
				return '';
			}

			$recipe_title = $spot['recipe_title'];

			// Primary Link Ziel bestimmen: plan | meal | recipe
			switch ( strtolower( trim( (string) $atts['primary_target'] ) ) ) {
				case 'meal':
					$primary_link = $spot['meal_url'] ?: $spot['recipe_url'];
					break;

				case 'recipe':
					$primary_link = $spot['recipe_url'];
					break;

				case 'plan':
				default:
					$primary_link = $spot['plan_url'] ?: home_url( '' );
					break;
			}

			$img = Mixmymeals_Meal_Plan::get_random_recipe_image_from_latest_plan(
				$atts['image_size'],
				array(
					'class'   => 'mmm-masthead__img',
					'loading' => 'eager',
					'alt'     => $recipe_title,
				)
			);

			// Eigener Wrapper für den Masthead, damit er separat gestylt werden kann.
			$meta_html = '' !== $spot['meta_html']
			? '<div class="mmm-masthead__meta">' . $spot['meta_html'] . '</div>'
			: '';

			$wrap_class = 'mmm-masthead ' . $atts['class'];

			ob_start();
			?>
		<section class="<?php echo esc_attr( $wrap_class ); ?>">
			<div class="mmm-masthead__inner">

				<?php if ( ! empty( $img ) ) : ?>
					<a class="mmm-masthead__media"
						href="<?php echo esc_url( $primary_link ); ?>"
						aria-label="<?php echo esc_attr( $recipe_title ); ?>">
						<?php echo wp_kses_post( $img ); ?>
					</a>
				<?php endif; ?>

				<div class="mmm-masthead__content">
					<?php if ( ! empty( $atts['eyebrow'] ) ) : ?>
						<span class="mmm-masthead__eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></span>
					<?php endif; ?>

					<h1 class="mmm-masthead__title">
						<?php echo esc_html( $atts['headline_prefix'] ); ?>
						<span class="mmm-masthead__recipe"><?php echo esc_html( $recipe_title ); ?></span><?php echo esc_html( $atts['headline_suffix'] ); ?>
					</h1>

					<?php
					// META-Leiste direkt unter der Headline
					echo wp_kses_post( $meta_html );
					?>

					<div class="mmm-masthead__cta">
						<a class="mmm-masthead__btn mmm-masthead__btn--primary"
							href="<?php echo esc_url( $primary_link ); ?>">
							<?php echo esc_html( $atts['primary_text'] ); ?>
						</a>

						<a class="mmm-masthead__btn mmm-masthead__btn--secondary"
							href="<?php echo esc_url( $atts['secondary_url'] ); ?>">
							<?php echo esc_html( $atts['secondary_text'] ); ?>
						</a>
					</div>
				</div>

			</div>
		</section>
			<?php
			return ob_get_clean();
		}
	);

}
