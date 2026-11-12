<?php

function mmm_render_equipment_required_for_a_single_recipe( $post_id = 0 ) {

	if ( shortcode_exists( 'mmm_equipment_required_for_a_single_recipe' ) ) {
		echo do_shortcode( '[mmm_equipment_required_for_a_single_recipe]' );
	}
}

/*
 * Renders cooking instructions for a single recipe.
 */
function mmm_render_recipe_steps_for_a_single_recipe( $post_id = 0 ) {

	if ( shortcode_exists( 'mmm_recipe_steps_for_a_single_recipe' ) ) {
		echo do_shortcode( '[mmm_recipe_steps_for_a_single_recipe]' );
	}
}

/*
 * Renders a table with nutrition tables for a single recipe.
 *
 */
function mmm_render_nutrition_table_for_a_single_recipe() {

	if ( shortcode_exists( 'mmm_nutrition_table_for_a_single_recipe' ) ) {
		echo do_shortcode( '[mmm_nutrition_table_for_a_single_recipe]' );
	}
}

/*
function mmm_render_add_recipe_to_meal_plan_cart_button() {

	if ( shortcode_exists( 'mmm_add_recipe_to_cart_button' ) ) {
		echo do_shortcode( '[mmm_add_recipe_to_cart_button]' );
	}

}
*/

/*
 * Renders actions links for a single recipe.
 */
function mmm_render_action_links_to_a_single_recipe() {

	if ( shortcode_exists( 'mmm_recipe_actions' ) ) {
		echo do_shortcode( '[mmm_recipe_actions]' );
	}
}

function mmm_render_recipe_disclaimer_for_a_single_recipe() {
	if ( shortcode_exists( 'mmm_disclaimer_for_a_single_recipe' ) ) {
		echo do_shortcode( '[mmm_disclaimer_for_a_single_recipe]' );
	}
}

function mmm_render_feature_image_for_a_single_recipe() {
	if ( shortcode_exists( 'mmm_feature_image_for_a_single_recipe' ) ) {
		echo do_shortcode( '[mmm_feature_image_for_a_single_recipe]' );
	}
}

/*
 * Renders the author box (avatar, name, bio) for a single recipe.
 */
function mmm_render_recipe_author_box() {
	if ( shortcode_exists( 'mmm_recipe_author_box' ) ) {
		echo do_shortcode( '[mmm_recipe_author_box]' );
	}
}

/*
 * Renders how often the recipe has been edited, als Absatz in der Hinweisbox
 * unter dem Disclaimer. Der Shortcode gibt '' aus, solange das Rezept seit
 * Einführung des Zählers nicht bearbeitet wurde (hide_empty) — dann bleibt auch
 * der Absatz weg, statt bei Bestandsrezepten eine nackte 0 zu zeigen.
 */
function mmm_render_recipe_edit_count() {

	if ( ! shortcode_exists( 'mmm_recipe_edit_count' ) ) {
		return;
	}

	$mmm_edit_count = trim( do_shortcode( '[mmm_recipe_edit_count]' ) );

	if ( '' === $mmm_edit_count ) {
		return;
	}

	printf(
		'<p class="mmm-recipe-edit-count">%s</p>',
		sprintf(
			/* translators: %s: number of times the recipe was edited. */
			esc_html__( 'This recipe is actively maintained. It has already been revised %s times.', 'mixmymeals-hello-child' ),
			'<span class="mmm-edit-count">' . esc_html( $mmm_edit_count ) . '</span>'
		)
	);
}

/*
 * Renders a standalone „Einplanen“-Button als Handlungsaufruf zwischen den
 * Abschnitten der Rezeptseite. Die Seite ist lang; der Button im Hero ist nach
 * dem ersten Scrollen aus dem Blick, deshalb steht er an den Stellen erneut, an
 * denen man sich üblicherweise entscheidet: nach den Zutaten, nach der
 * Zubereitung und am Ende.
 *
 * Gibt nichts aus, wenn der aktuelle Post kein Rezept ist.
 */
function mmm_render_recipe_plan_button() {

	if ( ! shortcode_exists( 'mmm_add_to_planner' ) ) {
		return;
	}

	$mmm_plan_button = trim( do_shortcode( '[mmm_add_to_planner element="button"]' ) );

	if ( '' === $mmm_plan_button ) {
		return;
	}

	echo '<div class="mmm-recipe-plan-cta">'
		. $mmm_plan_button // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the shortcode.
		. '</div>';
}

/*
 * Renders the hero (title, meta, actions) for a single recipe.
 */
function mmm_render_recipe_hero() {
	if ( shortcode_exists( 'mmm_recipe_hero' ) ) {
		echo do_shortcode( '[mmm_recipe_hero]' );
	}
}
