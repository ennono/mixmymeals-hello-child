<?php
/**
 * Einstellungen des Child Themes.
 *
 * Design → Theme-Einstellungen sammelt, was dieses Theme zu Astra hinzufügt:
 * die Reihenfolge und Sichtbarkeit der Abschnitte auf Einzelseiten und
 * Archiven und die selbst angelegten Abschnitte dazu.
 *
 * Aufteilung: eine Option je Feature (als Array, nicht ein Dutzend einzelner
 * Zeilen), die Seite sammelt die Abschnitte. Kommt später ein Feature dazu,
 * bekommt es seine eigene Option und einen eigenen Abschnitt auf derselben
 * Seite.
 *
 * @package MixMyMeals Astra Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MMM_CHILD_SETTINGS_PAGE  = 'mmm-theme-settings';
const MMM_CHILD_SETTINGS_GROUP = 'mmm_theme_settings_group';

/*
==========================================================
	Gemeinsames
	========================================================== */

/**
 * Gespeicherte Reihenfolge auf die Liste legen, die es wirklich gibt.
 *
 * Maßgeblich ist $known: was dort nicht (mehr) steht, fällt weg. Ein Eintrag,
 * der neu dazukommt, steht noch in keiner gespeicherten Reihenfolge — er
 * rutscht an die Stelle, an der er laut $known hingehört (hinter seinen
 * Vorgänger), statt ans Ende. Sonst müsste man nach jedem Update einmal
 * sortieren gehen.
 *
 * Zwei Nutzer: die Abschnitte der Einzelseiten und Archive
 * (mmm_single_section_order()) und die Buttonreihe im Plan-Hero
 * (mmm_plan_action_order()). Bei der zweiten ist es das Verhalten, auf das es
 * ankommt: ein Button, den ein Add-on beisteuert, erscheint an seiner Stelle
 * in der Liste des Plugins und nicht hinten.
 *
 * @param array $saved Gespeicherte Reihenfolge, beliebig verschmutzt.
 * @param array $known Die Einträge, die es gibt, in ihrer Standardreihenfolge.
 * @return string[]
 */
function mmm_child_merge_order( array $saved, array $known ): array {

	$order = array_values( array_unique( array_intersect( $saved, $known ) ) );

	foreach ( $known as $index => $slug ) {

		if ( in_array( $slug, $order, true ) ) {
			continue;
		}

		// Vorgänger aus $known, der schon einsortiert ist.
		$position = 0;
		for ( $i = $index - 1; $i >= 0; $i-- ) {
			$found = array_search( $known[ $i ], $order, true );
			if ( false !== $found ) {
				$position = $found + 1;
				break;
			}
		}

		array_splice( $order, $position, 0, array( $slug ) );
	}

	return $order;
}

/*
==========================================================
	Einstellungsseite
	========================================================== */

add_action( 'admin_menu', 'mmm_child_add_settings_page' );

/**
 * Design → Theme-Einstellungen. Bewusst nicht unter Einstellungen: dort liegt
 * die Seite des Plugins (Einstellungen → MixMyMeals), und das hier gehört zum
 * Theme.
 */
function mmm_child_add_settings_page(): void {
	add_theme_page(
		__( 'Theme Settings', 'mixmymeals-astra-child' ),
		__( 'Theme Settings', 'mixmymeals-astra-child' ),
		'edit_theme_options',
		MMM_CHILD_SETTINGS_PAGE,
		'mmm_child_render_settings_page'
	);
}

/*
==========================================================
	Abschnitte der Einzelseiten
	========================================================== */

add_action( 'admin_init', 'mmm_child_register_section_settings' );

/**
 * Reihenfolge und Sichtbarkeit der Abschnitte, je Seitentyp ein Abschnitt auf
 * dieser Seite. Welche Abschnitte es gibt, steht in includes/single-sections.php.
 */
function mmm_child_register_section_settings(): void {

	register_setting(
		MMM_CHILD_SETTINGS_GROUP,
		MMM_CHILD_SECTIONS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mmm_single_sections_sanitize',
			'default'           => array(),
		)
	);

	register_setting(
		MMM_CHILD_SETTINGS_GROUP,
		MMM_CHILD_CUSTOM_SECTIONS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mmm_custom_sections_sanitize',
			'default'           => array(),
		)
	);

	$first = true;

	foreach ( mmm_single_section_types() as $type => $label ) {

		$section_id = 'mmm_single_sections_' . $type;

		add_settings_section(
			$section_id,
			/* translators: %s: name of the single page, e.g. “Recipe page”. */
			sprintf( __( 'Sections: %s', 'mixmymeals-astra-child' ), $label ),
			static function () use ( $type, $first ) {

				if ( $first ) {
					echo '<p>' . esc_html__( 'Drag the sections into the order they should appear in. Unticked sections stay off the page; sections that have nothing to show stay hidden anyway.', 'mixmymeals-astra-child' ) . '</p>';
				}

				// Erklärt die Sonderregel der Archive an der Stelle, an der man
				// sie braucht: in der Liste, in der es diese Blöcke gibt.
				if ( mmm_archive_section_has_hero( $type ) ) {
					echo '<p>' . esc_html__( 'Archive description, search form and filter sit inside the archive header as long as they stay at the top of this list. Drag one below another block and it leaves the header and appears at its new place instead.', 'mixmymeals-astra-child' ) . '</p>';
				}
			},
			MMM_CHILD_SETTINGS_PAGE
		);

		add_settings_field(
			$section_id . '_order',
			__( 'Order', 'mixmymeals-astra-child' ),
			'mmm_child_render_section_order_field',
			MMM_CHILD_SETTINGS_PAGE,
			$section_id,
			array( 'type' => $type )
		);

		add_settings_field(
			$section_id . '_custom',
			__( 'Custom sections', 'mixmymeals-astra-child' ),
			'mmm_child_render_custom_sections_field',
			MMM_CHILD_SETTINGS_PAGE,
			$section_id,
			array( 'type' => $type )
		);

		$first = false;
	}
}

/**
 * Die sortierbare Liste eines Seitentyps.
 *
 * Die Reihenfolge steckt in den versteckten Feldern: sortable verschiebt das
 * <li> mitsamt seinem Feld, das Formular überträgt sie also in der Reihenfolge,
 * die man sieht. Ohne JavaScript bleibt die Liste stehen, die Häkchen wirken
 * aber weiterhin.
 *
 * @param array $args Siehe add_settings_field().
 */
function mmm_child_render_section_order_field( array $args ): void {

	$type     = (string) ( $args['type'] ?? '' );
	$registry = mmm_single_section_registry( $type );

	if ( empty( $registry ) ) {
		return;
	}

	$name = MMM_CHILD_SECTIONS_OPTION . '[' . $type . ']';

	printf( '<ul class="mmm-section-sort" data-type="%s">', esc_attr( $type ) );

	foreach ( mmm_single_section_order( $type ) as $slug ) {

		$label   = (string) ( $registry[ $slug ]['label'] ?? $slug );
		$checked = mmm_single_section_is_visible( $type, $slug );
		$id      = 'mmm_section_' . $type . '_' . $slug;
		$custom  = ! empty( $registry[ $slug ]['custom'] );

		printf(
			'<li class="mmm-section-sort__item%6$s" data-slug="%2$s">'
			. '<span class="mmm-section-sort__handle dashicons dashicons-menu" aria-hidden="true"></span>'
			. '<input type="hidden" name="%1$s[order][]" value="%2$s">'
			. '<input type="checkbox" id="%3$s" name="%1$s[show][%2$s]" value="1"%4$s>'
			. '<label for="%3$s">%7$s<span class="mmm-section-sort__label">%5$s</span></label>'
			. '</li>',
			esc_attr( $name ),
			esc_attr( $slug ),
			esc_attr( $id ),
			checked( $checked, true, false ),
			esc_html( $label ),
			$custom ? ' mmm-section-sort__item--custom' : '',
			// Bleistift vor der Beschriftung: selbst angelegt, steht weiter
			// unten auf der Seite als Editor.
			$custom ? '<span class="dashicons dashicons-edit" aria-hidden="true"></span>' : ''
		);
	}

	echo '</ul>';
}

/**
 * Die eigenen Abschnitte einer Seitenart: je Block eine Karte mit Titelfeld,
 * TinyMCE und Entfernen-Knopf, darunter der Knopf zum Hinzufügen.
 *
 * Hinzufügen und Entfernen macht JavaScript, ohne Umweg über den Server:
 * wp.editor.initialize() baut Wrap, Visuell/Code-Umschalter und Medienknopf
 * genauso auf, wie wp_editor() es serverseitig tut (siehe wp-admin/js/editor.js).
 * Wirksam wird beides beim Speichern — eine Karte, die nicht mehr im Formular
 * steht, kommt auch nicht mehr in der Option an.
 *
 * @param array $args Siehe add_settings_field().
 */
function mmm_child_render_custom_sections_field( array $args ): void {

	$type = (string) ( $args['type'] ?? '' );

	if ( '' === $type ) {
		return;
	}

	printf( '<div class="mmm-custom-sections" data-type="%s">', esc_attr( $type ) );

	foreach ( mmm_custom_sections( $type ) as $id => $section ) {
		mmm_child_render_custom_section_card( $type, (string) $id, (array) $section );
	}

	echo '</div>';

	printf(
		'<p><button type="button" class="button mmm-custom-add" data-type="%s">%s</button></p>',
		esc_attr( $type ),
		esc_html__( 'Add section', 'mixmymeals-astra-child' )
	);

	echo '<p class="description">'
		. esc_html__( 'New sections and removals take effect when you save. A section without a title and without text is not stored. Shortcodes in the text are executed on the page; an empty title means the section shows its text without a heading.', 'mixmymeals-astra-child' )
		. '</p>';
}

/**
 * Eine Karte. Dieselbe Form, die das JavaScript für einen neuen Abschnitt baut.
 *
 * @param array $section Gespeicherter Block.
 */
function mmm_child_render_custom_section_card( string $type, string $id, array $section ): void {

	$name      = MMM_CHILD_CUSTOM_SECTIONS_OPTION . '[' . $type . '][' . $id . ']';
	$editor_id = 'mmm_custom_' . $type . '_' . $id;
	$title     = (string) ( $section['title'] ?? '' );
	?>
	<div class="mmm-custom-section-card"
		data-type="<?php echo esc_attr( $type ); ?>"
		data-slug="<?php echo esc_attr( $id ); ?>"
		data-editor="<?php echo esc_attr( $editor_id ); ?>">

		<p>
			<label for="<?php echo esc_attr( $editor_id . '_title' ); ?>">
				<strong><?php esc_html_e( 'Title', 'mixmymeals-astra-child' ); ?></strong>
			</label><br>
			<input type="text"
				class="regular-text mmm-custom-title"
				id="<?php echo esc_attr( $editor_id . '_title' ); ?>"
				name="<?php echo esc_attr( $name ); ?>[title]"
				value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php
		wp_editor(
			(string) ( $section['content'] ?? '' ),
			$editor_id,
			array(
				'textarea_name' => $name . '[content]',
				'textarea_rows' => 8,
				'media_buttons' => true,
			)
		);
		?>

		<p>
			<button type="button" class="button-link delete mmm-custom-remove">
				<?php esc_html_e( 'Remove', 'mixmymeals-astra-child' ); ?>
			</button>
		</p>

	</div>
	<?php
}

/**
 * Reihenfolge und Häkchen säubern.
 *
 * Maßgeblich ist, was das Formular an Abschnitten kannte: ein Abschnitt, den
 * es dort nicht gab, darf hier nicht als „abgewählt" gelten — sonst würde ein
 * Speichern mit einem älteren Formular neue Abschnitte ausblenden.
 *
 * @param mixed $input Rohwerte aus dem Formular.
 * @return array<string, array{order: string[], hidden: string[]}>
 */
function mmm_single_sections_sanitize( $input ): array {

	$in  = is_array( $input ) ? $input : array();
	$out = array();

	foreach ( array_keys( mmm_single_section_types() ) as $type ) {

		$known = array_keys( mmm_single_section_registry( $type ) );

		$order = array_map( 'sanitize_key', (array) ( $in[ $type ]['order'] ?? array() ) );
		$order = array_values( array_unique( array_intersect( $order, $known ) ) );

		if ( isset( $in[ $type ]['show'] ) ) {

			// Aus dem Formular: angehakt heißt sichtbar.
			$shown  = array_map( 'sanitize_key', array_keys( (array) $in[ $type ]['show'] ) );
			$hidden = array_values( array_diff( $order, $shown ) );

		} else {

			/*
			 * Schon gesäubert. Beim allerersten Speichern gibt es die Option
			 * noch nicht: update_option() legt sie über add_option() an, und
			 * das schickt den Wert ein zweites Mal durch sanitize_option() —
			 * dann kommt hier das Ergebnis des ersten Durchgangs an, in dem es
			 * kein 'show' mehr gibt. Ohne diesen Zweig läse der zweite
			 * Durchgang jeden Abschnitt als abgewählt und die Seite wäre nach
			 * dem ersten Speichern leer.
			 */
			$hidden = array_map( 'sanitize_key', (array) ( $in[ $type ]['hidden'] ?? array() ) );
			$hidden = array_values( array_intersect( $hidden, $known ) );
		}

		$out[ $type ] = array(
			'order'  => $order,
			'hidden' => $hidden,
		);
	}

	return $out;
}

/*
==========================================================
	Buttons im Plan-Hero
	========================================================== */

add_action( 'admin_init', 'mmm_child_register_plan_action_settings' );

/**
 * Welche Buttons der Hero der Essensplan-Seite zeigt, in welcher Reihenfolge
 * und mit welcher Beschriftung. Welche Buttons es gibt, weiß das Plugin —
 * siehe includes/plan-actions.php.
 *
 * Nach den Abschnitten registriert, damit der Abschnitt auf der Seite unter
 * ihnen steht: do_settings_sections() gibt sie in der Reihenfolge ihrer
 * Anmeldung aus.
 */
function mmm_child_register_plan_action_settings(): void {

	register_setting(
		MMM_CHILD_SETTINGS_GROUP,
		MMM_CHILD_PLAN_ACTIONS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mmm_plan_actions_sanitize',
			'default'           => array(),
		)
	);

	add_settings_section(
		'mmm_plan_hero_actions',
		__( 'Buttons: Meal plan page', 'mixmymeals-astra-child' ),
		static function () {
			echo '<p>' . esc_html__( 'The buttons in the title block of a meal plan. Drag them into the order they should appear in; unticked buttons stay off the page.', 'mixmymeals-astra-child' ) . '</p>';
		},
		MMM_CHILD_SETTINGS_PAGE
	);

	add_settings_field(
		'mmm_plan_hero_actions_order',
		__( 'Buttons', 'mixmymeals-astra-child' ),
		'mmm_child_render_plan_actions_field',
		MMM_CHILD_SETTINGS_PAGE,
		'mmm_plan_hero_actions'
	);
}

/**
 * Die sortierbare Liste der Buttons.
 *
 * Dasselbe Markup wie mmm_child_render_section_order_field(), damit
 * assets/js/section-settings.js sie ohne eine Zeile mehr anfasst: das Skript
 * nimmt sich jede .mmm-section-sort und hängt jedem Eintrag die
 * Hoch/Runter-Knöpfe an. Dazu je Zeile ein Auswahlfeld für die Beschriftung.
 */
function mmm_child_render_plan_actions_field(): void {

	$registry = mmm_plan_action_registry();

	if ( empty( $registry ) ) {
		echo '<p>' . esc_html__( 'The buttons appear here as soon as the site has a meal plan — the list is the plugin’s, including what add-ons contribute.', 'mixmymeals-astra-child' ) . '</p>';
		return;
	}

	$name  = MMM_CHILD_PLAN_ACTIONS_OPTION;
	$modes = mmm_plan_action_label_modes();

	echo '<ul class="mmm-section-sort" data-type="plan_actions">';

	foreach ( mmm_plan_action_order( array_keys( $registry ) ) as $key ) {

		$label = (string) ( $registry[ $key ]['label'] ?? $key );
		$icon  = (string) ( $registry[ $key ]['icon'] ?? '' );
		$id    = 'mmm_plan_action_' . $key;

		printf(
			'<li class="mmm-section-sort__item" data-slug="%2$s">'
			. '<span class="mmm-section-sort__handle dashicons dashicons-menu" aria-hidden="true"></span>'
			. '<input type="hidden" name="%1$s[order][]" value="%2$s">'
			. '<input type="checkbox" id="%3$s" name="%1$s[show][%2$s]" value="1"%4$s>'
			. '<label for="%3$s"><span class="mmm-section-sort__label">%5$s</span></label>',
			esc_attr( $name ),
			esc_attr( $key ),
			esc_attr( $id ),
			checked( mmm_plan_action_is_visible( $key ), true, false ),
			esc_html( $label )
		);

		printf(
			'<select class="mmm-section-sort__mode" name="%1$s[labels][%2$s]" aria-label="%3$s">',
			esc_attr( $name ),
			esc_attr( $key ),
			/* translators: %s: name of the button, e.g. “Send via Email”. */
			esc_attr( sprintf( __( 'Caption of “%s”', 'mixmymeals-astra-child' ), $label ) )
		);

		foreach ( $modes as $mmm_mode => $mmm_mode_label ) {

			/*
			 * Ohne Icon gibt es nichts, was allein stehen könnte: das Plugin
			 * zeigt die Beschriftung dann ohnehin weiter (meal_plan_action_items()
			 * setzt `icon_only` nur mit Icon). Die Auswahl zu sperren ist
			 * ehrlicher als eine, die nichts tut.
			 */
			printf(
				'<option value="%1$s"%2$s%3$s>%4$s</option>',
				esc_attr( $mmm_mode ),
				selected( mmm_plan_action_label_mode( $key ), $mmm_mode, false ),
				'icon' === $mmm_mode && '' === $icon ? ' disabled' : '',
				esc_html( $mmm_mode_label )
			);
		}

		echo '</select></li>';
	}

	echo '</ul>';

	echo '<p class="description">'
		. esc_html__( 'A button without an icon cannot be shown as an icon alone. “Icon only” keeps the caption as the button’s name for screen readers and needs MixMyMeals 3.73.0.', 'mixmymeals-astra-child' )
		. ' '
		. esc_html__( '“Subscribe” leads to the subscribe form and therefore only appears while the section “Get this plan by e-mail” is on the page.', 'mixmymeals-astra-child' )
		. '</p>';
}

/**
 * Reihenfolge, Häkchen und Beschriftungen säubern.
 *
 * Die Reihenfolge wird hier nicht gegen die Registry gekürzt — anders als bei
 * den Abschnitten. Die Registry braucht einen Essensplan und ein laufendes
 * Plugin; wäre sie gerade leer, löschte ein Speichern die ganze gespeicherte
 * Reihenfolge. Beim Ausgeben legt mmm_plan_action_order() sie ohnehin auf die
 * Liste, die es wirklich gibt, ein unbekannter Schlüssel ist also harmlos.
 *
 * @param mixed $input Rohwerte aus dem Formular.
 * @return array{order: string[], hidden: string[], labels: array<string, string>}
 */
function mmm_plan_actions_sanitize( $input ): array {

	$in = is_array( $input ) ? $input : array();

	$order = array_map( 'sanitize_html_class', (array) ( $in['order'] ?? array() ) );
	$order = array_values( array_unique( array_filter( $order ) ) );

	$modes  = array_keys( mmm_plan_action_label_modes() );
	$labels = array();

	foreach ( (array) ( $in['labels'] ?? array() ) as $mmm_key => $mmm_mode ) {

		$mmm_key  = sanitize_html_class( (string) $mmm_key );
		$mmm_mode = (string) $mmm_mode;

		// Nur die Abweichungen: 'both' ist der Standard und braucht keine Zeile.
		if ( '' === $mmm_key || 'both' === $mmm_mode || ! in_array( $mmm_mode, $modes, true ) ) {
			continue;
		}

		$labels[ $mmm_key ] = $mmm_mode;
	}

	if ( isset( $in['show'] ) ) {

		// Aus dem Formular: angehakt heißt sichtbar.
		$shown  = array_map( 'sanitize_html_class', array_keys( (array) $in['show'] ) );
		$hidden = array_values( array_diff( $order, $shown ) );

	} else {

		/*
		 * Schon gesäubert — zum Grund siehe mmm_single_sections_sanitize():
		 * das erste Speichern läuft zweimal durch sanitize_option(), und im
		 * zweiten Durchgang gibt es kein 'show' mehr. Ohne diesen Zweig läse
		 * jeder Button als abgewählt und die Reihe wäre nach dem ersten
		 * Speichern leer.
		 */
		$hidden = array_map( 'sanitize_html_class', (array) ( $in['hidden'] ?? array() ) );
		$hidden = array_values( array_filter( $hidden ) );
	}

	return array(
		'order'  => $order,
		'hidden' => $hidden,
		'labels' => $labels,
	);
}

add_action( 'admin_enqueue_scripts', 'mmm_child_section_sort_assets' );

/**
 * Alles, was die Einstellungsseite an Skript und Stil braucht.
 *
 * Was das Skript tut, steht in assets/js/section-settings.js.
 *
 * @param string $hook Aktuelle Admin-Seite.
 */
function mmm_child_section_sort_assets( string $hook ): void {

	if ( 'appearance_page_' . MMM_CHILD_SETTINGS_PAGE !== $hook ) {
		return;
	}

	wp_enqueue_style( 'dashicons' );

	/*
	 * wp.editor.initialize() und der Medienknopf für Abschnitte, die erst im
	 * Browser entstehen. wp_enqueue_editor() liefert TinyMCE, Quicktags und
	 * wp.editor.getDefaultSettings() — ohne das baut initialize() nichts auf.
	 */
	wp_enqueue_editor();
	wp_enqueue_media();

	/*
	 * Eigene Datei statt eingebettetem Skript: der Browser kann sie
	 * zwischenspeichern, und die Version ist der Zeitstempel der Datei — wie
	 * beim Stylesheet des Themes, aus demselben Grund (siehe functions.php).
	 */
	$mmm_js = get_stylesheet_directory() . '/assets/js/section-settings.js';

	wp_enqueue_script(
		'mmm-section-settings',
		get_stylesheet_directory_uri() . '/assets/js/section-settings.js',
		array( 'jquery', 'jquery-ui-sortable', 'editor' ),
		file_exists( $mmm_js )
			? (string) filemtime( $mmm_js )
			: CHILD_THEME_MIXMYMEALS_ASTRA_CHILD_VERSION,
		true
	);

	wp_localize_script(
		'mmm-section-settings',
		'mmmSectionSort',
		array(
			'up'            => __( 'Move up', 'mixmymeals-astra-child' ),
			'down'          => __( 'Move down', 'mixmymeals-astra-child' ),
			'title'         => __( 'Title', 'mixmymeals-astra-child' ),
			'remove'        => __( 'Remove', 'mixmymeals-astra-child' ),
			'removeConfirm' => __( 'Really remove?', 'mixmymeals-astra-child' ),
			'untitled'      => __( 'Custom section', 'mixmymeals-astra-child' ),
		)
	);

	wp_add_inline_style(
		'common',
		<<<'CSS'
.mmm-section-sort { margin: 0; max-width: 34em; }
.mmm-section-sort__item {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0 0 4px;
	padding: 8px 10px;
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
}
.mmm-section-sort__handle { color: #8c8f94; cursor: grab; }
.mmm-section-sort__item.ui-sortable-helper { box-shadow: 0 2px 8px rgba( 0, 0, 0, .15 ); }
.mmm-section-sort__placeholder {
	height: 39px;
	margin: 0 0 4px;
	border: 1px dashed #c3c4c7;
	border-radius: 4px;
}
.mmm-section-sort[data-type="plan_actions"] { max-width: 40em; }
.mmm-section-sort__mode { margin-left: auto; }
.mmm-section-sort__move { display: flex; gap: 2px; margin-left: auto; }
.mmm-section-sort__move .button-link { color: #50575e; line-height: 1; }
.mmm-section-sort__move .dashicons { font-size: 16px; width: 16px; height: 16px; }
.mmm-section-sort__item--custom .dashicons-edit { color: #8c8f94; font-size: 16px; width: 16px; height: 16px; }
.mmm-custom-section-card {
	max-width: 46em;
	margin: 0 0 16px;
	padding: 12px 14px;
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
}
.mmm-custom-section-card p:first-child { margin-top: 0; }
.mmm-custom-section-card p:last-child { margin-bottom: 0; }
.mmm-unsaved {
	position: sticky;
	top: 32px;
	z-index: 20;
	margin: 16px 0;
	padding: 1px 12px;
	background: #fff;
}
/* Auf schmalen Bildschirmen ist die Adminleiste höher. */
@media screen and (max-width: 782px) {
	.mmm-unsaved { top: 46px; }
}
.mmm-unsaved__save { margin-left: 8px; }
.mmm-custom-remove { color: #b32d2e; }
.mmm-custom-remove:hover, .mmm-custom-remove:focus { color: #8a2424; }
CSS
	);
}

/**
 * Die Seite selbst.
 */
function mmm_child_render_settings_page(): void {

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	/*
	 * „Einstellungen gespeichert." Auf einer eigenen Seite unter Design druckt
	 * WordPress die Meldung nicht von sich aus — options.php legt sie ab, wer
	 * sie zeigen will, muss sie abholen. Ohne das speichert man und die Seite
	 * sagt nichts dazu.
	 *
	 * Ohne Slug: die Meldung hängt an 'general', nicht an der Optionsgruppe
	 * dieser Seite (wp-admin/options.php). Nach der Gruppe gefragt, kommt
	 * nichts zurück.
	 */
	settings_errors();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Settings', 'mixmymeals-astra-child' ); ?></h1>

		<p>
			<?php
			esc_html_e(
				'Settings this child theme adds on top of Astra.',
				'mixmymeals-astra-child'
			);
			?>
		</p>

		<form action="options.php" method="post" id="mmm-theme-settings-form">

			<?php
			/*
			 * Bleibt verborgen, bis am Formular etwas geändert wurde; das
			 * Skript blendet sie ein (assets/js/section-settings.js). Die Seite
			 * ist lang, und Sortierlisten und Editoren sehen nicht danach aus,
			 * als müsste man unten noch speichern — die Leiste klebt deshalb
			 * oben mit, sobald es etwas zu speichern gibt.
			 *
			 * „Jetzt speichern" ist ein echter Submit: er speichert auch ohne
			 * JavaScript und ist zugleich das, was die Eingabetaste auslöst.
			 * Das form-Attribut ist die Absicherung, falls die Leiste doch
			 * einmal aus dem Formular wandert — ein Submit außerhalb seines
			 * Formulars täte sonst nichts.
			 *
			 * Die Klasse „inline" ist Pflicht: WordPress sammelt sonst jede
			 * .notice ein und hängt sie oben an .wp-header-end, also aus dem
			 * Formular heraus (wp-admin/js/common.js).
			 */
			?>
			<div class="mmm-unsaved notice notice-warning inline" hidden>
				<p>
					<strong><?php esc_html_e( 'Unsaved changes!', 'mixmymeals-astra-child' ); ?></strong>
					<button type="submit" form="mmm-theme-settings-form" class="button-link mmm-unsaved__save">
						<?php esc_html_e( 'Save now', 'mixmymeals-astra-child' ); ?>
					</button>
				</p>
			</div>

			<?php
			settings_fields( MMM_CHILD_SETTINGS_GROUP );
			do_settings_sections( MMM_CHILD_SETTINGS_PAGE );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
