<?php
/**
 * Eigene Abschnitte: Blöcke, die es nicht im Theme gibt, sondern die man unter
 * Design → Theme-Einstellungen selbst anlegt — Titel, Text aus TinyMCE,
 * Shortcodes inklusive.
 *
 * Angelegt und entfernt werden sie im Browser; gespeichert wird, was beim
 * Absenden im Formular steht.
 *
 * Sie hängen sich in dieselbe Registry wie die eingebauten Abschnitte
 * (includes/single-sections.php) und verhalten sich danach wie jeder andere
 * Block: sie stehen in derselben Sortierliste, lassen sich abhaken und
 * verschieben.
 *
 * Ein eigener Abschnitt gehört zu genau einer Seitenart. Denselben Text auf
 * zwei Seitenarten heißt: zweimal anlegen. Das ist Absicht — ein Block, der an
 * mehreren Stellen erscheint, müsste überall dieselbe Reihenfolge, aber
 * getrennte Sichtbarkeit haben, und das erklärt sich in einer Liste nicht mehr.
 *
 * @package MixMyMeals Astra Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MMM_CHILD_CUSTOM_SECTIONS_OPTION = 'mmm_custom_sections';

/*
==========================================================
	Daten
	========================================================== */

/**
 * Alle eigenen Abschnitte, nach Seitenart.
 *
 * @return array<string, array<string, array{title: string, content: string}>>
 */
function mmm_custom_sections_all(): array {

	$saved = get_option( MMM_CHILD_CUSTOM_SECTIONS_OPTION, array() );

	return is_array( $saved ) ? $saved : array();
}

/**
 * Die eigenen Abschnitte einer Seitenart.
 *
 * @return array<string, array{title: string, content: string}>
 */
function mmm_custom_sections( string $type ): array {

	$all = mmm_custom_sections_all();

	return isset( $all[ $type ] ) && is_array( $all[ $type ] )
		? $all[ $type ]
		: array();
}

/**
 * Kennung für einen neuen Abschnitt.
 *
 * Zufällig statt durchgezählt: eine gelöschte Nummer dürfte nie wieder
 * vergeben werden, sonst erbt ein neuer Abschnitt die Position und das
 * Häkchen seines Vorgängers, der längst weg ist. Vergeben wird sie im
 * Browser (mmm_child_section_sort_assets()), wenn dort eine Karte entsteht;
 * diese Funktion ist der Gegenpart für Code, der einen Abschnitt anlegt.
 */
function mmm_custom_section_new_id(): string {
	return 'custom_' . bin2hex( random_bytes( 4 ) );
}

/*
==========================================================
	Registry
	========================================================== */

// Priorität 20: die Archive füllen ihre Registry bei 10 und ersetzen sie dabei
// komplett — die eigenen Abschnitte kommen danach dazu.
add_filter( 'mmm_single_section_registry', 'mmm_custom_sections_into_registry', 20, 2 );

/**
 * Eigene Abschnitte in die Registry ihrer Seitenart hängen.
 *
 * Sie stehen am Ende, also erscheint ein neu angelegter Abschnitt zunächst
 * unten auf der Seite, bis man ihn verschiebt.
 *
 * @param array  $sections Eingebaute Abschnitte.
 * @param string $type     Seitenart.
 * @return array
 */
function mmm_custom_sections_into_registry( array $sections, string $type ): array {

	foreach ( mmm_custom_sections( $type ) as $id => $section ) {

		$title = trim( (string) ( $section['title'] ?? '' ) );

		$sections[ $id ] = array(
			'label'  => '' !== $title
				? $title
				: __( 'Custom section', 'mixmymeals-astra-child' ),
			'render' => static function () use ( $type, $id ) {
				mmm_render_custom_section( $type, $id );
			},
			'custom' => true,
		);
	}

	return $sections;
}

/**
 * Einen eigenen Abschnitt ausgeben.
 *
 * Titel leer heißt: kein Abschnittskopf, nur der Text. Der Titel steht dann
 * nur in der Sortierliste, damit man den Block dort wiedererkennt.
 */
function mmm_render_custom_section( string $type, string $id ): void {

	$section = mmm_custom_sections( $type )[ $id ] ?? null;

	if ( null === $section ) {
		return;
	}

	$title   = trim( (string) ( $section['title'] ?? '' ) );
	$content = trim( (string) ( $section['content'] ?? '' ) );

	if ( '' === $title && '' === $content ) {
		return;
	}

	/*
	 * Erst Absätze, dann Shortcodes — dieselbe Reihenfolge wie bei einem
	 * Beitrag (wpautop hängt bei 10 am the_content-Filter, do_shortcode bei
	 * 11). Umgekehrt bekäme die Ausgabe eines Shortcodes nachträglich Absätze
	 * verpasst, die er nicht wollte.
	 *
	 * shortcode_unautop() dazwischen, wie in WordPress selbst: ein Shortcode,
	 * der allein in einer Zeile steht, steckt sonst hinterher in einem <p> —
	 * und gibt er wie die Beitragsreihen ein <section> aus, bricht der Browser
	 * das <p> davor auf und lässt ein leeres zurück.
	 *
	 * Gesäubert wird beim Speichern, nicht hier: was in der Option steht, hat
	 * mmm_custom_sections_sanitize() bereits durch wp_kses_post() geschickt.
	 */
	$html = '' !== $content
		? do_shortcode( shortcode_unautop( wpautop( $content ) ) )
		: '';

	$html = (string) apply_filters( 'mmm_custom_section_content', $html, $type, $id );
	?>
	<section class="mmm-custom-section">
		<?php
		if ( '' !== $title ) {
			mmm_single_section_head( $title );
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- beim Speichern gesäubert.
		?>
	</section>
	<?php
}

/*
==========================================================
	Speichern
	========================================================== */

/**
 * Eingaben säubern.
 *
 * Kümmert sich zugleich um „hinzufügen" und „entfernen": beides sind Felder
 * des Formulars, es gibt keinen zweiten Speicherweg.
 *
 * @param mixed $input Rohwerte aus dem Formular.
 * @return array<string, array<string, array{title: string, content: string}>>
 */
function mmm_custom_sections_sanitize( $input ): array {

	$in    = is_array( $input ) ? $input : array();
	$out   = array();
	$types = array_keys( mmm_single_section_types() );

	foreach ( $types as $type ) {

		foreach ( (array) ( $in[ $type ] ?? array() ) as $id => $section ) {

			$id = sanitize_key( (string) $id );

			if ( '' === $id || ! is_array( $section ) ) {
				continue;
			}

			$title   = sanitize_text_field( (string) ( $section['title'] ?? '' ) );
			$content = (string) ( $section['content'] ?? '' );

			/*
			 * Wie bei einem Beitrag: wer HTML unbeschränkt schreiben darf,
			 * darf es auch hier. Alle anderen bekommen wp_kses_post(), sonst
			 * wäre diese Einstellungsseite ein Weg, die Beschränkung zu
			 * umgehen.
			 */
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$content = wp_kses_post( $content );
			}

			// Ohne Titel und ohne Text gibt es nichts zu speichern.
			if ( '' === $title && '' === trim( $content ) ) {
				continue;
			}

			$out[ $type ][ $id ] = array(
				'title'   => $title,
				'content' => $content,
			);
		}
	}

	return $out;
}
