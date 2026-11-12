<?php

/**
 * Renders all meals of a single meal plan.
 *
 * @param int|string|null $meal_plan_id Meal plan ID, 'current' or null (defaults to current post).
 * @param bool            $show_dates   Reserved for future use.
 *
 * @return string HTML output
 */
function mmm_render_meal_plan_meals(
	int|string|null $meal_plan_id = null,
	bool $show_dates = false
) {

	if ( ! shortcode_exists( 'mmm_meals_in_a_single_meal_plan' ) ) {
		return;
	}

	// Plan ohne Mahlzeiten: eigener Wortlaut statt der Plugin-Vorgabe. Der Text
	// steht im Theme, weil er zur Ansprache der Seite gehört.
	$mmm_empty_text = __(
		'For this plan no meals have been planned yet. Take a look at the other plans in the meantime.',
		'mixmymeals-hello-child'
	);

	echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
		'[mmm_meals_in_a_single_meal_plan empty_text="' . esc_attr( $mmm_empty_text ) . '"]'
	);
}


/*
 * Renders a shopping list for a meal plean based on the recipes it contains.
 *
 * Mit der Adresse des Planers: über der Liste steht das Personenfeld des
 * Plugins, und sein Link „Im Planer anpassen“ lädt den Plan dorthin. Ohne
 * cart_url sucht das Skript den Planer-Link im Kopf der Seite — den gibt es
 * nur, wo das Menü ihn trägt.
 */
function mmm_render_meal_plan_shopping_list() {

	if ( ! shortcode_exists( 'mmm_shopping_list_for_a_single_meal_plan' ) ) {
		return;
	}

	echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
		'[mmm_shopping_list_for_a_single_meal_plan cart_url="' . esc_attr( mmm_planner_page_url() ) . '"]'
	);
}

/**
 * Adresse der Planer-Seite: die erste veröffentlichte Seite mit dem Template
 * page-templates/planer.php. Leer, wenn es keine gibt.
 *
 * @return string
 */
function mmm_planner_page_url(): string {

	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	$pages = get_pages(
		array(
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one small query per request.
			'meta_value'  => 'page-templates/planer.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'post_status' => 'publish',
			'number'      => 1,
		)
	);

	$url = $pages ? (string) get_permalink( $pages[0] ) : '';

	return $url;
}

/*
 * Renders a link thats prints the meal plan.
 */
/*
function mmm_render_print_meal_plan_link() {

	if ( shortcode_exists( 'mmm_print_meal_plan_pdf' ) ) {
		echo do_shortcode( '[mmm_print_meal_plan_pdf]' );
	}

}
*/

/*
 * Renders a link thats prints the meal plan.
 */
function mmm_render_action_links_for_a_single_meal_plan() {

	if ( shortcode_exists( 'mmm_meal_plan_actions' ) ) {
		echo do_shortcode( '[mmm_meal_plan_actions]' );
	}
}


function mmm_render_meta_for_a_single_meal_plan() {

	if ( shortcode_exists( 'mmm_meta_for_a_single_meal_plan' ) ) {
		echo do_shortcode( '[mmm_meta_for_a_single_meal_plan]' );
	}
}
