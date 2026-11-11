<?php
/**
 * Template Name: Planer mit Rezeptvorschlägen
 *
 * Wie Astras Standard-Seitenvorlage, nur bringt die Vorlage den Planer selbst
 * mit: [mmm_meal_plan_builder], darunter die Live-Nährwerte des Plans
 * ([mmm_planner_nutrition]), seine Live-Einkaufsliste
 * ([mmm_planner_shopping_list]). Darunter eine eigene Box „Plan speichern
 * (und abonnieren)“ mit den drei Aktionen Speichern, Teilen und Leeren,
 * gefolgt von Rezeptvorschlägen zum direkten Befüllen des Plans. Solange die
 * Box (#mmm-plan-save) nicht zu sehen ist, führt eine Leiste am unteren Rand
 * zu ihr (vom Plugin gebaut, hier gestaltet) — der einzige Link dorthin.
 *
 * Zwei verschiedene Ebenen, absichtlich:
 *
 * - Der Planer geht über the_content und landet damit in .entry-content.clear,
 *   genau dort, wo er bisher als Shortcode im Seiteninhalt stand. Sein Markup
 *   bleibt dadurch unverändert.
 * - Die Speichern-Box und die Vorschläge stehen direkt in #primary, also
 *   außerhalb des Artikels — dieselbe Ebene wie
 *   mmm_render_recipe_browse_card() im Rezept-Archiv. Nur so sind sie eigene
 *   Boxen auf der Seite statt Teil des Seiteninhalts.
 *
 * Der Seiteninhalt wird weiterhin ausgegeben (unter dem Planer, im Artikel),
 * damit sich der Seite später Text hinzufügen lässt. Die Planer-Shortcodes
 * gehören dann nicht mehr in den Editor — sie kämen sonst doppelt.
 *
 * @package MixMyMeals Astra Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mmm_planer_save_section' ) ) {
	/**
	 * Speichern und Abonnieren als eigene Box, gebaut wie die Rezeptreihen
	 * darunter („Neue Rezepte“): section.mmm-recipe-browse mit derselben
	 * Kopfzeile (.mmm-recipe-row__header, h2.mmm-recipe-row__title), damit
	 * sie als Abschnitt gleichen Rangs auf der Seite steht und nicht als
	 * Anhang an die Einkaufsliste.
	 *
	 * Ziel der Leiste am unteren Rand (#mmm-plan-save).
	 * data-plan-save sagt dem Plugin, welches Element es beobachtet: solange
	 * es nicht zu sehen ist, zeigt frontend.js die Leiste „Plan speichern“.
	 *
	 * Die Überschrift sagt „und abonnieren“ nur, wenn die Box die Abo-Felder
	 * auch zeigt: das Plugin entscheidet das an genau einer Stelle, für sein
	 * Formular, seinen AJAX-Handler und hier.
	 *
	 * Einmal gerendert und gemerkt, falls sie mehr als einmal gefragt wird.
	 *
	 * @return string '' ohne Plugin.
	 */
	function mmm_planer_save_section(): string {

		static $html = null;

		if ( null !== $html ) {
			return $html;
		}

		// „Plan speichern“, „Planer teilen“ und „Plan leeren“ in dieser
		// Reihenfolge, weil Teilen zwischen den beiden anderen stehen soll —
		// drei eigene Shortcodes, die Hülle (.mmm-plan-actions) kommt von
		// hier, damit sie als eine Zeile gestylt werden können.
		$actions = '';
		foreach ( array( 'mmm_create_meal_plan_from_cart', 'mmm_share_planner', 'mmm_clear_cart' ) as $name ) {
			if ( shortcode_exists( $name ) ) {
				$actions .= do_shortcode( '[' . $name . ']' );
			}
		}

		if ( '' === $actions ) {
			$html = '';
			return $html;
		}

		$label = is_callable( array( 'Mixmymeals_Shortcodes', 'plan_subscribe_offer_mode' ) )
			&& 'off' !== Mixmymeals_Shortcodes::plan_subscribe_offer_mode()
				? __( 'Save and subscribe to the plan', 'mixmymeals-astra-child' )
				: __( 'Save plan', 'mixmymeals-astra-child' );

		$html = '<section class="mmm-recipe-browse mmm-plan-save" id="mmm-plan-save" data-plan-save aria-labelledby="mmm-plan-save-title">'
			. '<div class="mmm-recipe-row__header">'
			. '<h2 class="mmm-recipe-row__title" id="mmm-plan-save-title">' . esc_html( $label ) . '</h2>'
			. '</div>'
			. '<div class="mmm-plan-actions">' . $actions . '</div>'
			. '</section>';

		return $html;
	}
}

/*
 * Priorität 9, also vor wpautop (10), shortcode_unautop (10) und do_shortcode
 * (11): Die Shortcodes durchlaufen damit exakt dieselbe Filterkette wie zuvor
 * im Editorinhalt und werden identisch gerendert.
 */
add_filter(
	'the_content',
	static function ( $content ) {

		if ( ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		// Baut einen Shortcode-Aufruf, aber nur wenn es den Shortcode gibt:
		// ohne Plugin bleibt die Seite leer statt rohe Klammern zu zeigen.
		$tag = static function ( string $name, string $title = '' ): string {

			if ( ! shortcode_exists( $name ) ) {
				return '';
			}

			return '' === $title
				? '[' . $name . ']'
				: '[' . $name . ' title="' . esc_attr( $title ) . '"]';
		};

		// Die Nährwerte des entstehenden Plans zwischen Board und Speichern:
		// erst sehen, was der Plan liefert, dann speichern. Das Plugin füllt
		// den Kasten bei jeder Änderung neu; leer bleibt er unsichtbar.
		$parts = array(
			$tag( 'mmm_meal_plan_builder' ),
			$tag( 'mmm_planner_nutrition' ),
			// Die Einkaufsliste des entstehenden Plans, genauso live: was
			// für den Plan einzukaufen ist, schon bevor er gespeichert wird.
			$tag( 'mmm_planner_shopping_list' ),
			(string) $content,
		);

		// Leerzeilen zwischen den Blöcken: wpautop und shortcode_unautop
		// erwarten alleinstehende Shortcodes, sonst bleibt ein <p> übrig.
		return implode( "\n\n", array_filter( $parts, 'strlen' ) );
	},
	9
);

get_header();
?>

<?php if ( astra_page_layout() === 'left-sidebar' ) { ?>

	<?php get_sidebar(); ?>

<?php } ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<?php astra_content_page_loop(); ?>

		<?php
		// Speichern und Abonnieren: eigene Box in #primary, vor den
		// Rezeptvorschlägen und gebaut wie sie.
		echo mmm_planer_save_section(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function.
		?>

		<?php
		/*
		 * Rezeptvorschläge als eigene Boxen unter dem Planer. Hier statt im
		 * Seiteninhalt, damit sie — wie im Rezept-Archiv — direkt in #primary
		 * sitzen und als eigenständige Karten auf der Seite stehen.
		 *
		 * Jede Zeile bringt ihre eigene geboxte <section> mit und rendert '',
		 * wenn sie nichts zu zeigen hat; leere Boxen verschwinden also von
		 * selbst. Die Karten enthalten „Zum Planer hinzufügen“, der Plan lässt
		 * sich damit ohne Seitenwechsel füllen.
		 */
		$mmm_planer_rows = array(
			'mmm_new_recipes'      => __( 'New recipes', 'mixmymeals-astra-child' ),
			'mmm_favorite_recipes' => __( 'Our Favorites', 'mixmymeals-astra-child' ),
		);

		foreach ( $mmm_planer_rows as $mmm_shortcode => $mmm_title ) {

			if ( ! shortcode_exists( $mmm_shortcode ) ) {
				continue;
			}

			echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
				'[' . $mmm_shortcode . ' title="' . esc_attr( $mmm_title ) . '"]'
			);
		}
		?>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() === 'right-sidebar' ) { ?>

	<?php get_sidebar(); ?>

<?php } ?>

<?php
get_footer();
