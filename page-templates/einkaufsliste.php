<?php
/**
 * Template Name: Einkaufsliste
 *
 * Die Einkaufsliste des Plans, der gerade im Planer liegt, als eigene Seite.
 *
 * Bisher war die Liste ein Abschnitt tief im Planer (mobil ab rund 4 000 px).
 * Einkaufen ist aber ein eigener Schritt, an einem anderen Ort und oft Tage
 * nach dem Planen; die Tab-Leiste führt deshalb direkt hierher.
 *
 * Die Liste selbst kommt unverändert aus dem Plugin
 * ([mmm_planner_shopping_list]), also dieselbe, die der Planer zeigt. Die
 * Seite hängt am Plan des Besuchers (Cookie) und gehört nicht in den Index.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Persönlicher Inhalt: nicht indexieren. Yoast setzt dasselbe über das
// Seiten-Meta; der Filter gilt auch ohne Yoast.
add_filter( 'wp_robots', 'wp_robots_no_robots' );

// Die Seite trägt den Titel schon als H1; die Zwischenüberschrift des
// Plugins über der Liste wäre dieselbe Zeile ein zweites Mal.
add_filter(
	'mixmymeals_planner_heading',
	static function ( $text, $key ) {
		return 'shopping' === $key ? '' : $text;
	},
	20,
	2
);

if ( ! function_exists( 'mmm_shopping_page_body' ) ) {
	/**
	 * Liste oder, bei leerem Plan, der Weg zu einem Plan.
	 *
	 * @return string
	 */
	function mmm_shopping_page_body(): string {

		$has_items = class_exists( 'Mixmymeals_Cart' ) && ( new Mixmymeals_Cart() )->get_items();

		if ( $has_items && shortcode_exists( 'mmm_planner_shopping_list' ) ) {

			$planner = function_exists( 'mmm_planner_page_url' ) ? mmm_planner_page_url() : '';
			$back    = '' === $planner
				? ''
				: '<p class="mmm-shopping-page__back"><a href="' . esc_url( $planner ) . '">'
					. '<i class="fa-solid fa-pen" aria-hidden="true"></i> '
					. esc_html__( 'Edit plan', 'mixmymeals-hello-child' ) . '</a></p>';

			return '<div class="mmm-shopping-page" data-shopping-check>' . $back
				. do_shortcode( '[mmm_planner_shopping_list]' ) . '</div>';
		}

		$links = '';

		$recipes = post_type_exists( 'mmm_recipe' ) ? get_post_type_archive_link( 'mmm_recipe' ) : false;
		if ( $recipes ) {
			$links .= '<a class="mmm-btn mmm-btn--primary" href="' . esc_url( $recipes ) . '">'
				. esc_html__( 'Browse recipes', 'mixmymeals-hello-child' ) . '</a>';
		}

		$plans = post_type_exists( 'mmm_meal_plan' ) ? get_post_type_archive_link( 'mmm_meal_plan' ) : false;
		if ( $plans ) {
			$links .= '<a class="mmm-btn mmm-btn--ghost" href="' . esc_url( $plans ) . '">'
				. esc_html__( 'Pick a ready-made plan', 'mixmymeals-hello-child' ) . '</a>';
		}

		return '<div class="mmm-shopping-page mmm-shopping-page--empty">'
			. '<p class="mmm-shopping-page__empty-title">'
			. esc_html__( 'Your plan is still empty.', 'mixmymeals-hello-child' ) . '</p>'
			. '<p>' . esc_html__( 'Add recipes to your plan and your shopping list appears here.', 'mixmymeals-hello-child' ) . '</p>'
			. ( '' === $links ? '' : '<p class="mmm-shopping-page__actions">' . $links . '</p>' )
			. '</div>';
	}
}

add_filter(
	'the_content',
	static function ( $content ) {

		if ( ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		return mmm_shopping_page_body() . "\n\n" . (string) $content;
	},
	9
);

get_header();
?>

	<div id="primary" <?php mmm_primary_class(); ?>>
		<?php mmm_primary_content_top(); ?>
		<?php mmm_content_page_loop(); ?>
		<?php mmm_primary_content_bottom(); ?>
	</div><!-- #primary -->

<?php
get_footer();
