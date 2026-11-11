/**
 * Design → Theme-Einstellungen: Abschnitte sortieren und eigene Abschnitte
 * anlegen.
 *
 * Sortieren per Maus (jQuery UI, in WordPress enthalten) und per Tastatur:
 * zwei Knöpfe je Zeile, die dieses Skript nachträgt. Ein Griff, den man nur
 * ziehen kann, wäre für Tastaturbedienung nicht erreichbar.
 *
 * Beschriftungen kommen aus mmmSectionSort (wp_localize_script), die Markup-
 * Form einer Karte steht in mmm_child_render_custom_section_card() — was hier
 * gebaut wird, muss dazu passen.
 */

jQuery( function ( $ ) {

	var form = $( '#mmm-theme-settings-form' );

	/* ----- Ungespeicherte Änderungen ----- */

	/*
	 * Die Leiste steht schon im Markup und ist verborgen. Einmal eingeblendet
	 * bleibt sie stehen, bis gespeichert wird — sie zählt keine Änderungen,
	 * sie sagt nur, dass es welche gibt.
	 */
	function markDirty() {
		$( '.mmm-unsaved' ).prop( 'hidden', false );
	}

	// Alles, was im Formular selbst passiert: Felder, Häkchen, Auswahllisten.
	form.on( 'input change', ':input', markDirty );

	/*
	 * TinyMCE schreibt in einem iframe, dessen Ereignisse das Formular nie
	 * erreichen. Jeder Editor wird deshalb einzeln abgehört — die schon
	 * vorhandenen und die, die später im Browser entstehen. Erst ab „init",
	 * sonst gölte schon das Einsetzen des gespeicherten Textes als Änderung.
	 */
	function watchEditor( editor ) {
		editor.on( 'init', function () {
			editor.on( 'input keyup change ExecCommand', markDirty );
		} );

		if ( editor.initialized ) {
			editor.on( 'input keyup change ExecCommand', markDirty );
		}
	}

	if ( window.tinymce ) {
		$.each( window.tinymce.editors, function ( index, editor ) {
			watchEditor( editor );
		} );

		window.tinymce.on( 'AddEditor', function ( event ) {
			watchEditor( event.editor );
		} );
	}

	/* ----- Sortieren ----- */

	function moveButtons() {
		return $( '<span class="mmm-section-sort__move"></span>' ).append(
			$( '<button type="button" class="button-link mmm-section-sort__up"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' )
				.attr( 'aria-label', mmmSectionSort.up ),
			$( '<button type="button" class="button-link mmm-section-sort__down"><span class="dashicons dashicons-arrow-down-alt2"></span></button>' )
				.attr( 'aria-label', mmmSectionSort.down )
		);
	}

	function move( button, direction ) {

		var item      = $( button ).closest( '.mmm-section-sort__item' );
		var neighbour = 'up' === direction
			? item.prev( '.mmm-section-sort__item' )
			: item.next( '.mmm-section-sort__item' );

		if ( ! neighbour.length ) {
			return;
		}

		if ( 'up' === direction ) {
			item.insertBefore( neighbour );
		} else {
			item.insertAfter( neighbour );
		}

		button.focus();
		markDirty();
	}

	$( '.mmm-section-sort' ).each( function () {

		$( this ).sortable( {
			handle:               '.mmm-section-sort__handle',
			axis:                 'y',
			placeholder:          'mmm-section-sort__placeholder',
			forcePlaceholderSize: true,
			cursor:               'grabbing',
			update:               markDirty
		} );

		$( this ).find( '.mmm-section-sort__item' ).append( moveButtons() );
	} );

	$( document ).on( 'click', '.mmm-section-sort__up', function () {
		move( this, 'up' );
	} );

	$( document ).on( 'click', '.mmm-section-sort__down', function () {
		move( this, 'down' );
	} );

	/* ----- Eigene Abschnitte ----- */

	/*
	 * TinyMCE hält seinen Inhalt im iframe und schreibt ihn erst beim Absenden
	 * in das textarea zurück. Das hier ist der Gürtel zum Hosenträger: wird der
	 * Rückschritt aus irgendeinem Grund nicht ausgelöst, ginge alles verloren,
	 * was im visuellen Tab geschrieben wurde — der Code-Tab arbeitet direkt im
	 * textarea und wäre als Einziger heil.
	 */
	form.on( 'submit', function () {
		if ( window.tinymce ) {
			window.tinymce.triggerSave();
		}
	} );

	function newSlug() {
		var chars = 'abcdef0123456789';
		var out   = '';

		for ( var i = 0; i < 8; i++ ) {
			out += chars.charAt( Math.floor( Math.random() * chars.length ) );
		}

		return 'custom_' + out;
	}

	function sortRow( type, slug, label ) {

		var list = $( '.mmm-section-sort[data-type="' + type + '"]' );

		if ( ! list.length ) {
			return;
		}

		var base = 'mmm_single_sections[' + type + ']';
		var id   = 'mmm_section_' + type + '_' + slug;

		var row = $( '<li class="mmm-section-sort__item mmm-section-sort__item--custom">' )
			.attr( 'data-slug', slug )
			.append( '<span class="mmm-section-sort__handle dashicons dashicons-menu" aria-hidden="true"></span>' )
			.append( $( '<input type="hidden">' ).attr( { name: base + '[order][]', value: slug } ) )
			.append( $( '<input type="checkbox" value="1" checked>' ).attr( { id: id, name: base + '[show][' + slug + ']' } ) )
			.append(
				$( '<label>' ).attr( 'for', id )
					.append( '<span class="dashicons dashicons-edit" aria-hidden="true"></span>' )
					.append( $( '<span class="mmm-section-sort__label">' ).text( label ) )
			)
			.append( moveButtons() );

		list.append( row );
		list.sortable( 'refresh' );
	}

	$( document ).on( 'click', '.mmm-custom-add', function () {

		var type     = String( $( this ).data( 'type' ) );
		var slug     = newSlug();
		var editorId = 'mmm_custom_' + type + '_' + slug;
		var name     = 'mmm_custom_sections[' + type + '][' + slug + ']';

		var card = $( '<div class="mmm-custom-section-card">' ).attr( {
			'data-type':   type,
			'data-slug':   slug,
			'data-editor': editorId
		} );

		card.append(
			$( '<p>' )
				.append( $( '<label>' ).attr( 'for', editorId + '_title' ).append( $( '<strong>' ).text( mmmSectionSort.title ) ) )
				.append( '<br>' )
				.append( $( '<input type="text" class="regular-text mmm-custom-title">' ).attr( { id: editorId + '_title', name: name + '[title]' } ) )
		);

		card.append(
			$( '<textarea class="wp-editor-area" rows="8">' ).attr( { id: editorId, name: name + '[content]' } )
		);

		card.append(
			$( '<p>' ).append(
				$( '<button type="button" class="button-link delete mmm-custom-remove">' ).text( mmmSectionSort.remove )
			)
		);

		$( '.mmm-custom-sections[data-type="' + type + '"]' ).append( card );

		wp.editor.initialize( editorId, {
			tinymce:      { wpautop: true },
			quicktags:    true,
			mediaButtons: true
		} );

		sortRow( type, slug, mmmSectionSort.untitled );
		markDirty();

		card.find( '.mmm-custom-title' ).trigger( 'focus' );
	} );

	// Zwei Klicks. Der Text ist weg, sobald man speichert, und ein Fehlgriff
	// wäre nicht rückgängig zu machen.
	$( document ).on( 'click', '.mmm-custom-remove', function () {

		var button = $( this );

		if ( ! button.data( 'armed' ) ) {

			button.data( 'armed', true ).text( mmmSectionSort.removeConfirm );

			window.setTimeout( function () {
				button.data( 'armed', false ).text( mmmSectionSort.remove );
			}, 4000 );

			return;
		}

		var card = button.closest( '.mmm-custom-section-card' );

		if ( window.wp && wp.editor && wp.editor.remove ) {
			try {
				wp.editor.remove( String( card.data( 'editor' ) ) );
			} catch ( e ) {}
		}

		$( '.mmm-section-sort__item[data-slug="' + card.data( 'slug' ) + '"]' ).remove();
		card.remove();
		markDirty();
	} );

	// Der Titel benennt den Block auch in der Sortierliste.
	$( document ).on( 'input', '.mmm-custom-title', function () {

		var card  = $( this ).closest( '.mmm-custom-section-card' );
		var label = $.trim( this.value ) || mmmSectionSort.untitled;

		$( '.mmm-section-sort__item[data-slug="' + card.data( 'slug' ) + '"] .mmm-section-sort__label' ).text( label );
	} );
} );
