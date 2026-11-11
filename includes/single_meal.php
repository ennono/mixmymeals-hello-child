<?php

/*
 * Renders link the leads back to the parent meal plan.
 */
function mmm_render_back_to_meal_plan_link_for_a_single_meal() {

	if ( shortcode_exists( 'mmm_back_to_meal_plan_link_for_single_meal' ) ) {
		echo do_shortcode( '[mmm_back_to_meal_plan_link_for_single_meal]' );
	}
}

/*
 * Renders the author box of the recipe this meal plans, directly below the
 * hero — same card and same position as on the recipe page. The shortcode
 * resolves the meal's recipe itself and returns '' when there is none.
 */
function mmm_render_recipe_author_box_for_a_single_meal() {

	if ( shortcode_exists( 'mmm_recipe_author_box' ) ) {
		echo do_shortcode( '[mmm_recipe_author_box]' );
	}
}

/*
 * Renders meta information for a single meal.
 */
function mmm_render_meta_for_a_single_meal() {

	if ( shortcode_exists( 'mmm_meta_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_meta_for_a_single_meal]' );
	}
}

/*
 * Renders a list of equipment required for a meal.
 */
function mmm_render_equipment_required_for_a_single_meal() {

	if ( shortcode_exists( 'mmm_equipment_required_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_equipment_required_for_a_single_meal]' );
	}
}

/*
 * Renders instructions to cook a single meal.
 */
function mmm_render_scaled_recipe_steps_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_scaled_recipe_steps_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_scaled_recipe_steps_for_a_single_meal]' );
	}
}

/*
 * Renders the button that starts the cooking mode for a single meal.
 */
function mmm_render_cook_mode_button_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_cook_mode_button_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_cook_mode_button_for_a_single_meal]' );
	}
}

/*
 * Renders a table with nutrition details for a single meal.
 */
function mmm_render_meal_nutrition_table_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_meal_nutrition_table_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_meal_nutrition_table_for_a_single_meal]' );
	}
}

/*
 * Renders a table with nutrition details for a single meal.
 */
function mmm_render_ingredients_a_single_meal() {
	if ( shortcode_exists( 'mmm_meal_ingredients' ) ) {
		echo do_shortcode( '[mmm_meal_ingredients]' );
	}
}

function mmm_render_recipe_disclaimer_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_disclaimer_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_disclaimer_for_a_single_meal]' );
	}
}

function mmm_render_feature_image_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_feature_image_for_a_single_meal' ) ) {
		echo do_shortcode( '[mmm_feature_image_for_a_single_meal]' );
	}
}

function mmm_render_meal_calculator_for_a_single_meal() {
	if ( shortcode_exists( 'mmm_meal_nutrition_calculator' ) ) {
		echo do_shortcode( '[mmm_meal_nutrition_calculator]' );
	}
}
