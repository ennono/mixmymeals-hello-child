<?php
/**
 * Die Suche der Website: gruppierte Ergebnisse über Rezepte, Pläne, Zutaten
 * und Küchengeräte.
 *
 * Wie gesucht und gerankt wird, entscheidet das Plugin (Mixmymeals_Search:
 * Titel, dann Zutaten bzw. Rezepte im Plan, dann Volltext). Das Theme sagt
 * nur, wie die Gruppen heißen, und gibt die Treffer aus — es kennt weder
 * Meta-Schlüssel noch die Beziehung Mahlzeit → Plan.
 *
 * @package MixMyMeals Astra Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Der Suchbegriff der aktuellen Anfrage.
 */
function mmm_search_term(): string {
	return trim( (string) get_search_query() );
}

/**
 * Die Gruppen in der Reihenfolge, in der sie erscheinen, mit ihrem Namen.
 *
 * Welche Gruppen es gibt und in welcher Reihenfolge, sagt das Plugin
 * (Rezepte, Pläne, Zutaten, Geräte); das Theme gibt ihnen nur Namen. Eine
 * Gruppe ohne Namen hier erscheint nicht.
 *
 * @return array<string, array{label: string}>
 */
function mmm_search_groups(): array {

	if ( ! class_exists( 'Mixmymeals_Search' ) ) {
		return array();
	}

	$labels = array(
		'recipe'     => __( 'Recipes', 'mixmymeals-astra-child' ),
		'plan'       => __( 'Meal plans', 'mixmymeals-astra-child' ),
		'ingredient' => __( 'Ingredients', 'mixmymeals-astra-child' ),
		'equipment'  => __( 'Equipment', 'mixmymeals-astra-child' ),
	);

	$groups = array();

	foreach ( array_keys( Mixmymeals_Search::groups() ) as $key ) {
		if ( isset( $labels[ $key ] ) ) {
			$groups[ $key ] = array( 'label' => $labels[ $key ] );
		}
	}

	return $groups;
}

/**
 * Die Treffer einer Gruppe, fertig sortiert und ohne Dubletten.
 *
 * @return int[]
 */
function mmm_search_ids( string $group, string $term ): array {

	return class_exists( 'Mixmymeals_Search' )
		? Mixmymeals_Search::ids( $group, $term )
		: array();
}

/**
 * Der Link auf alle Treffer einer Gruppe — bei Rezepten und Plänen die
 * Archivsuche, sonst das Archiv selbst. Das Plugin kennt den Parameter.
 */
function mmm_search_group_link( string $group, string $term ): string {

	return class_exists( 'Mixmymeals_Search' )
		? Mixmymeals_Search::archive_link( $group, $term )
		: '';
}
