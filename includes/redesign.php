<?php
/**
 * Redesign: was die Seiten um den Planer herum ordnet.
 *
 * Der Planer ist das Produkt, die Rezeptübersicht der Weg dorthin. Diese
 * Datei hält die Stellen zusammen, an denen das Theme dafür in die Ausgabe
 * eingreift: eine Suche statt zwei, die Einleitung unter den Ergebnissen,
 * „Mehr laden“ statt Blättern und der Wochenplan als Seitenspalte am
 * Schreibtisch. Die Bedienlogik selbst liegt im Plugin (frontend.js), hier
 * stehen nur Platzierung und Markup.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Eine Suche: Das Plugin sucht über sein Titelfeld jetzt auch in den Zutaten.
 * Das zweite Feld („Das habe ich da“) entfällt damit im Formular.
 */
add_filter( 'mixmymeals_archive_search_ingredient_field', '__return_false' );

/**
 * Ob die aktuelle Seite die ungefilterte Rezeptübersicht ist.
 *
 * @return bool
 */
function mmm_is_plain_recipe_archive(): bool {

	return is_post_type_archive( 'mmm_recipe' )
		&& ! ( function_exists( 'mmm_archive_facet' ) && ! empty( mmm_archive_facet() ) );
}

/**
 * Die Einleitung der Rezeptübersicht steht unter den Ergebnissen.
 *
 * Über der Liste schob sie das erste Rezept auf dem Handy in den zweiten
 * Bildschirm. Wer die Seite öffnet, will Rezepte sehen; der Text bleibt für
 * alle, die bis unten lesen, und für Suchmaschinen.
 */
function mmm_move_recipe_archive_intro(): void {

	if ( ! mmm_is_plain_recipe_archive() ) {
		return;
	}

	add_action(
		'mmm_primary_content_bottom',
		static function (): void {

			ob_start();
			mmm_print_archive_description_from_option();
			$intro = trim( (string) ob_get_clean() );

			if ( '' !== $intro ) {
				echo '<section class="mmm-archive-intro">' . $intro . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where it is built.
			}
		},
		20
	);
}
add_action( 'wp', 'mmm_move_recipe_archive_intro', 20 );

/**
 * Ob die Seite den Wochenplan als Seitenspalte zeigt: überall dort, wo man
 * Rezepte einplant, aber nicht im Planer selbst.
 *
 * @return bool
 */
function mmm_shows_side_plan(): bool {

	if ( ! class_exists( 'Mixmymeals_Planner_Tools' ) ) {
		return false;
	}

	return is_post_type_archive( array( 'mmm_recipe', 'mmm_meal_plan' ) )
		|| is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) )
		|| is_singular( array( 'mmm_recipe', 'mmm_meal_plan' ) );
}

/**
 * Der Wochenplan am rechten Rand (nur am Schreibtisch sichtbar, siehe CSS).
 * Das Plugin füllt [data-mini-plan] und hält es aktuell.
 */
function mmm_render_side_plan(): void {

	if ( ! mmm_shows_side_plan() ) {
		return;
	}
	?>
	<aside class="mmm-side-plan" aria-label="<?php esc_attr_e( 'Your week', 'mixmymeals-hello-child' ); ?>">
		<details class="mmm-side-plan__box" open>
			<summary class="mmm-side-plan__toggle">
				<i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Your week', 'mixmymeals-hello-child' ); ?></span>
			</summary>
			<div class="mmm-mini-plan" data-mini-plan></div>
		</details>
	</aside>
	<?php
}
add_action( 'wp_footer', 'mmm_render_side_plan', 4 );

/**
 * Body-Klasse für Seiten mit Seitenspalte.
 *
 * @param string[] $classes Body-Klassen.
 * @return string[]
 */
function mmm_side_plan_body_class( $classes ) {

	if ( mmm_shows_side_plan() ) {
		$classes[] = 'mmm-has-side-plan';
	}

	return $classes;
}
add_filter( 'body_class', 'mmm_side_plan_body_class' );

/**
 * Das kleine Skript des Themes: „Mehr laden“ unter Archivlisten.
 */
function mmm_enqueue_theme_script(): void {

	$file = get_stylesheet_directory() . '/assets/js/theme.js';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_script(
		'mmm-theme',
		get_stylesheet_directory_uri() . '/assets/js/theme.js',
		array(),
		(string) filemtime( $file ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'mmm-theme',
		'mmmTheme',
		array(
			'more'    => __( 'Show more', 'mixmymeals-hello-child' ),
			'loading' => __( 'Loading…', 'mixmymeals-hello-child' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mmm_enqueue_theme_script', 20 );
