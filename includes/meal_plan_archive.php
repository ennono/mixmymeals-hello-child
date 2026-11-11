<?php

/*
 * Adds meta information to posts in meal plan archive.
 * To be used with a hook.
 */
function mmm_add_meta_to_meal_plan_archive_posts() {

	if ( shortcode_exists( 'mmm_meta_for_posts_in_meal_plan_archive' ) ) {
		echo do_shortcode( '[mmm_meta_for_posts_in_meal_plan_archive] ' );
	}
}

/*
 * Adds thumbnails of included recipes to posts in meal plan archive.
 * To be used with a hook.
 */
function mmm_add_thumbnails_to_meal_plan_archive_posts() {

	if ( shortcode_exists( 'mmm_thumbnails_of_meals_in_a_single_meal_plan' ) ) {
		echo do_shortcode( '[mmm_thumbnails_of_meals_in_a_single_meal_plan] ' );
	}
}

/*
 * Prints the weekdays of a card in the meal plan archive in one row with
 * the plan's number ("#4812"), the same label the plan page shows beside
 * its title, set at the row's end. Replaces the weekday overview's own hook
 * so the two share a wrapper. Without either, nothing is printed.
 */
function mmm_add_weekdays_and_number_to_meal_plan_archive_posts() {

	if ( 'mmm_meal_plan' !== get_post_type() ) {
		return;
	}

	$weekdays = shortcode_exists( 'mmm_weekday_overview_for_posts_in_meal_plan_archive' )
		? do_shortcode( '[mmm_weekday_overview_for_posts_in_meal_plan_archive]' )
		: '';
	$number   = shortcode_exists( 'mmm_meal_plan_number' )
		? do_shortcode( '[mmm_meal_plan_number]' )
		: '';

	if ( '' === trim( $weekdays . $number ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the plugin's shortcodes.
	echo '<div class="mmm-plan-card-head">' . $weekdays . $number . '</div>';
}

/*
 * Prints a meal type overview for posts in meal plan archive.
 */
function mmm_add_meal_type_overview_meal_plan_archive_posts() {

	if ( shortcode_exists( 'mmm_meal_type_overview_for_posts_in_meal_plan_archive' ) ) {
		echo do_shortcode( '[mmm_meal_type_overview_for_posts_in_meal_plan_archive]' );
	}
}

/*
 * Prints the meal plan category filter:
 */
function mmm_print_meal_plan_category_filter() {

	if ( shortcode_exists( 'mmm_meal_plan_category_switcher' ) ) {
		echo do_shortcode( '[mmm_meal_plan_category_switcher] ' );
	}
}
