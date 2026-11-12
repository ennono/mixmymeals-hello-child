<?php
/**
 * Die Buttonreihe im Hero der Essensplan-Seite (.mmm-plan-hero__actions).
 *
 * Welche Buttons es gibt, weiß das Plugin:
 * Mixmymeals_Shortcodes::meal_plan_action_items() führt „Per E-Mail senden“,
 * „Zu Bring! exportieren“ und „Anpassen“ und lässt Add-ons über
 * mixmymeals_meal_plan_actions weitere beisteuern (das PDF des Printers).
 * Diese Datei entscheidet nur, welche davon erscheinen, in welcher
 * Reihenfolge und ob sie ihre Beschriftung zeigen — Seitenaufbau und
 * Gestaltung, also Sache des Themes.
 *
 * Eingestellt wird das unter Design → Theme-Einstellungen; das Formular dazu
 * steht in includes/theme-settings.php.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MMM_CHILD_PLAN_ACTIONS_OPTION = 'mmm_plan_hero_actions';

/*
==========================================================
	Einstellungen lesen
	========================================================== */

/**
 * Die drei Beschriftungs-Modi, Schlüssel => Beschriftung im Formular.
 *
 * @return array<string, string>
 */
function mmm_plan_action_label_modes(): array {
	return array(
		'both' => __( 'Icon + text', 'mixmymeals-hello-child' ),
		'icon' => __( 'Icon only', 'mixmymeals-hello-child' ),
		'text' => __( 'Text only', 'mixmymeals-hello-child' ),
	);
}

/**
 * Gespeicherte Einstellungen, roh.
 *
 * @return array{order?: string[], hidden?: string[], labels?: array<string, string>}
 */
function mmm_plan_action_settings(): array {

	$saved = get_option( MMM_CHILD_PLAN_ACTIONS_OPTION, array() );

	return is_array( $saved ) ? $saved : array();
}

/**
 * Reihenfolge der Buttons.
 *
 * Sortiert wird gegen die Schlüssel, die es im Augenblick wirklich gibt, nicht
 * gegen die gespeicherte Liste: so geht der Button eines Add-ons nicht
 * verloren, nur weil ihn noch niemand gespeichert hat.
 *
 * @param string[] $known Vorhandene Schlüssel in der Reihenfolge des Plugins.
 * @return string[]
 */
function mmm_plan_action_order( array $known ): array {

	$settings = mmm_plan_action_settings();

	$order = mmm_child_merge_order(
		(array) ( $settings['order'] ?? array() ),
		$known
	);

	return (array) apply_filters( 'mmm_plan_action_order', $order, $known );
}

/**
 * Ist der Button eingeschaltet? Ausgeblendet wird als Liste gespeichert, nicht
 * als „sichtbar“-Liste: ein Button, den ein Add-on später beisteuert, ist
 * damit da, ohne dass jemand ihn erst freischalten muss.
 */
function mmm_plan_action_is_visible( string $key ): bool {

	$settings = mmm_plan_action_settings();
	$hidden   = (array) ( $settings['hidden'] ?? array() );

	return (bool) apply_filters(
		'mmm_plan_action_is_visible',
		! in_array( $key, $hidden, true ),
		$key
	);
}

/**
 * Beschriftungs-Modus eines Buttons: 'both', 'icon' oder 'text'.
 *
 * Gespeichert werden nur die Abweichungen, Standard ist 'both' — aus demselben
 * Grund, aus dem die versteckten Buttons als Liste stehen.
 */
function mmm_plan_action_label_mode( string $key ): string {

	$settings = mmm_plan_action_settings();
	$mode     = (string) ( $settings['labels'][ $key ] ?? 'both' );

	if ( ! array_key_exists( $mode, mmm_plan_action_label_modes() ) ) {
		$mode = 'both';
	}

	return $mode;
}

/*
==========================================================
	Anwenden
	========================================================== */

add_filter( 'mixmymeals_meal_plan_actions', 'mmm_plan_hero_actions' );

/**
 * Die Einstellungen auf die Liste des Plugins legen.
 *
 * 'icon' setzt `icon_only` (das Plugin behält die Beschriftung als
 * zugänglichen Namen des Buttons, ab MixMyMeals 3.73.0), 'text' nimmt dem Item
 * sein Icon — dafür braucht es keine Plugin-Version, der Hero gibt das <i> nur
 * aus, wenn `icon` gefüllt ist.
 *
 * @param mixed $items Aktionen des Plugins, Schlüssel => Item.
 * @return array
 */
function mmm_plan_hero_actions( $items ): array {

	$items = is_array( $items ) ? $items : array();
	$out   = array();

	/*
	 * „Abonnieren“ springt auf das Formular im Abschnitt „Diesen Plan per
	 * E-Mail erhalten“. Ist der Abschnitt abgewählt, steht das Formular nicht
	 * auf der Seite und der Sprung ginge ins Leere — dass es die Abos
	 * überhaupt gibt, hat das Plugin schon geprüft, aber ob dieses Theme das
	 * Formular ausgibt, weiß nur das Theme.
	 */
	if ( isset( $items['subscribe'] ) && ! mmm_single_section_is_visible( 'plan', 'subscribe' ) ) {
		unset( $items['subscribe'] );
	}

	foreach ( mmm_plan_action_order( array_keys( $items ) ) as $key ) {

		if ( ! mmm_plan_action_is_visible( $key ) ) {
			continue;
		}

		$item = $items[ $key ];

		switch ( mmm_plan_action_label_mode( $key ) ) {
			case 'icon':
				$item['icon_only'] = true;
				break;

			case 'text':
				$item['icon'] = '';
				break;
		}

		$out[ $key ] = $item;
	}

	return $out;
}

/*
==========================================================
	Registry für das Formular
	========================================================== */

/**
 * Die Buttons, die es gibt, mit Beschriftung und Icon — nur für die
 * Einstellungsseite.
 *
 * Gelesen wird sie beim Plugin, nie hier wiederholt: so steht der Button eines
 * Add-ons von selbst im Formular.
 *
 * @return array<string, array{label: string, icon: string}>
 */
function mmm_plan_action_registry(): array {

	if ( ! method_exists( 'Mixmymeals_Shortcodes', 'meal_plan_action_items' ) ) {
		return array();
	}

	$plan_id = mmm_plan_action_sample_plan();

	if ( 0 === $plan_id ) {
		return array();
	}

	/*
	 * Ohne den eigenen Filter. Liefe er mit, fehlte ein abgeschalteter Button
	 * in seiner eigenen Zeile — und ließe sich nie wieder einschalten.
	 */
	remove_filter( 'mixmymeals_meal_plan_actions', 'mmm_plan_hero_actions' );
	$items = (array) Mixmymeals_Shortcodes::meal_plan_action_items( $plan_id );
	add_filter( 'mixmymeals_meal_plan_actions', 'mmm_plan_hero_actions' );

	$registry = array();

	foreach ( $items as $key => $item ) {
		$registry[ (string) $key ] = array(
			'label' => (string) ( $item['label'] ?? $key ),
			'icon'  => (string) ( $item['icon'] ?? '' ),
		);
	}

	return $registry;
}

/**
 * Irgendein Essensplan, an dem sich die Liste aufbauen lässt.
 *
 * meal_plan_action_items() baut echte Adressen (Bring!-Deeplink, mailto mit
 * Titel) und braucht dafür einen Plan. Welcher, ist für das Formular
 * gleichgültig — gefragt sind nur die Schlüssel und Beschriftungen.
 *
 * @return int 0, wenn es keinen Plan gibt.
 */
function mmm_plan_action_sample_plan(): int {

	foreach ( array( 'publish', 'any' ) as $mmm_status ) {

		$mmm_ids = get_posts(
			array(
				'post_type'        => 'mmm_meal_plan',
				'post_status'      => $mmm_status,
				'numberposts'      => 1,
				'fields'           => 'ids',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			)
		);

		if ( ! empty( $mmm_ids ) ) {
			return (int) $mmm_ids[0];
		}
	}

	return 0;
}
