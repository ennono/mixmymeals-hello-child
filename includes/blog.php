<?php
/**
 * Blogbeiträge: Helfer für Einzelansicht und Archiv.
 *
 * Die Seite besteht sonst aus eigenen Inhaltstypen — Rezepte, Pläne, Zutaten —
 * und der gewöhnliche WordPress-Beitrag lief bis hierher in Astras Vorlagen
 * mit. Das sah nicht falsch aus (das Stylesheet fasst `.single-post` und
 * Astras Blog-Raster längst an), aber es sah eben auch nicht nach dieser Seite
 * aus: kein Hero mit Chips, keine Karten im Vokabular der Archive.
 *
 * Hier steht nur, was beide Vorlagen brauchen. Das Markup selbst lebt in
 * single.php, archive.php und home.php.
 *
 * @package MixMyMeals Hello Child
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wörter pro Minute für die Lesezeit.
 *
 * 200 ist der übliche Mittelwert für deutsche Fließtexte. Die Zahl ist ein
 * Versprechen an den Leser und keine Messung — darum wird sie unten auf volle
 * Minuten gerundet und nie unter eine Minute gedrückt.
 */
const MMM_BLOG_WORDS_PER_MINUTE = 200;

/**
 * Lesezeit eines Beitrags in Minuten.
 *
 * Auf dem rohen Inhalt, ohne Shortcodes und ohne `the_content` — zwei Gründe:
 * im Archiv stünde sie auf jeder Karte und würde dort den halben Inhalt der
 * Seite rendern, Einbettungen und Datenbankabfragen eingeschlossen. Und ein
 * eingebettetes Rezept ist ein Kasten, den man überfliegt, keine Minute Text.
 *
 * Gezählt wird mit einem Unicode-Ausdruck, nicht mit str_word_count(): das
 * kennt nur ASCII-Buchstaben und macht aus „Dünndarm" zwei Wörter — bei
 * deutschem Fließtext ein Aufschlag von gut einem Zehntel.
 *
 * @param WP_Post|int|null $post Beitrag, Standard: der aktuelle.
 * @return int Minuten, mindestens 1.
 */
function mmm_blog_reading_time( $post = null ): int {

	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return 1;
	}

	$text  = wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) );
	$words = preg_match_all( '/[\p{L}\p{N}]+/u', $text );

	return max( 1, (int) round( $words / MMM_BLOG_WORDS_PER_MINUTE ) );
}

/**
 * Die Meta-Chips eines Beitrags: Datum, Lesezeit, Kategorien.
 *
 * Dasselbe Vokabular wie bei Rezept und Plan (`mmm-meta__item--*`), damit ein
 * Beitrag im selben Raster gelesen wird wie alles andere. Kategorien sind
 * verlinkt, Datum und Lesezeit nicht — sie führen nirgendwohin.
 *
 * @param WP_Post|int|null $post  Beitrag, Standard: der aktuelle.
 * @param bool             $terms Kategorien mitnehmen (im Archiv kürzer: nein).
 * @return string Fertiges, escaptes Markup; '' wenn es nichts zu sagen gibt.
 */
function mmm_blog_meta_chips( $post = null, bool $terms = true ): string {

	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$items = array();

	$items[] = sprintf(
		'<span class="mmm-meta__item mmm-meta__item--date"><time datetime="%1$s">%2$s</time></span>',
		esc_attr( (string) get_the_date( DATE_W3C, $post ) ),
		esc_html( (string) get_the_date( '', $post ) )
	);

	$minutes = mmm_blog_reading_time( $post );

	$items[] = sprintf(
		'<span class="mmm-meta__item mmm-meta__item--reading-time">%s</span>',
		esc_html(
			sprintf(
				/* translators: %s: number of minutes. */
				_n( '%s min read', '%s min read', $minutes, 'mixmymeals-hello-child' ),
				number_format_i18n( $minutes )
			)
		)
	);

	if ( $terms ) {

		$categories = get_the_category( $post->ID );

		if ( $categories && ! is_wp_error( $categories ) ) {

			$links = array();

			foreach ( $categories as $category ) {
				$links[] = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( (string) get_category_link( $category ) ),
					esc_html( $category->name )
				);
			}

			$items[] = '<span class="mmm-meta__item mmm-meta__item--category">'
				. implode( ', ', $links ) . '</span>';
		}
	}

	return '<div class="mmm-meta mmm-post-meta">' . implode( '', $items ) . '</div>';
}

/**
 * Eine Beitragskarte fürs Archiv.
 *
 * Ein Link um die ganze Karte, nicht nur um den Titel: die Karte ist das
 * Ziel, und ein Treffer, bei dem nur drei Wörter klickbar sind, ist auf einem
 * Handy kaum zu treffen. Der Titel bleibt trotzdem ein eigenes Element, damit
 * die Überschriftenebene im Archiv stimmt.
 *
 * @param WP_Post|int|null $post Beitrag, Standard: der aktuelle.
 * @return void
 */
function mmm_blog_render_card( $post = null ): void {

	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$permalink = (string) get_permalink( $post );

	/*
	 * Astras Klassen fliegen raus. Das Elternthema hängt über post_class() sein
	 * Blograster an jeden Beitrag (`ast-article-post`, `ast-grid-common-col`),
	 * und dort steht `width: 33.33%` — in Astras eigener Schleife richtig, in
	 * dieser Karte verheerend: sie schrumpfte auf ein Drittel ihrer Rasterspalte
	 * und brach den Titel auf ein Wort je Zeile. Die WordPress-Klassen
	 * (post-123, type-post, category-…) bleiben, daran hängen Plugins.
	 */
	$classes = array_filter(
		get_post_class( 'mmm-post-card', $post->ID ),
		static fn( string $class ): bool => 0 !== strpos( $class, 'ast-' )
	);
	?>
	<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<a class="mmm-post-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo get_the_post_thumbnail( $post, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		<?php endif; ?>

		<div class="mmm-post-card__body">

			<?php echo mmm_blog_meta_chips( $post, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

			<h2 class="mmm-post-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h2>

			<?php $mmm_excerpt = trim( (string) get_the_excerpt( $post ) ); ?>
			<?php if ( '' !== $mmm_excerpt ) : ?>
				<p class="mmm-post-card__excerpt"><?php echo esc_html( $mmm_excerpt ); ?></p>
			<?php endif; ?>

		</div>

	</article>
	<?php
}

/**
 * Option, in der die Blogbeschreibung steht.
 *
 * Eine Theme-Option und keine des Plugins: das Plugin beschreibt seine eigenen
 * Archive (Rezepte, Pläne, Zutaten, Geräte) und kennt keine Beiträge.
 */
const MMM_BLOG_DESCRIPTION_OPTION = 'mmm_child_blog_description';

add_action( 'admin_init', 'mmm_blog_register_description_setting' );

/**
 * Das Feld unter Design → Theme-Einstellungen.
 *
 * Eigener Abschnitt auf derselben Seite, wie die Datei es oben vorschreibt:
 * ein Feature, eine Option, ein Abschnitt.
 */
function mmm_blog_register_description_setting(): void {

	register_setting(
		MMM_CHILD_SETTINGS_GROUP,
		MMM_BLOG_DESCRIPTION_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'default'           => '',
		)
	);

	add_settings_section(
		'mmm_blog_description',
		__( 'Blog', 'mixmymeals-hello-child' ),
		static function (): void {
			echo '<p>' . esc_html__( 'Stands under the heading of the blog archive, the way the recipe and meal plan archives carry theirs. A category with its own description keeps that one.', 'mixmymeals-hello-child' ) . '</p>';
		},
		MMM_CHILD_SETTINGS_PAGE
	);

	add_settings_field(
		'mmm_blog_description_text',
		__( 'Blog description', 'mixmymeals-hello-child' ),
		static function (): void {
			printf(
				'<textarea name="%1$s" id="%1$s" rows="4" class="large-text">%2$s</textarea>',
				esc_attr( MMM_BLOG_DESCRIPTION_OPTION ),
				esc_textarea( (string) get_option( MMM_BLOG_DESCRIPTION_OPTION, '' ) )
			);
		},
		MMM_CHILD_SETTINGS_PAGE,
		'mmm_blog_description'
	);
}

/**
 * Der Suchbegriff über dem Beitragsarchiv.
 *
 * Eigener Parameter statt `s`: `s` schaltet WordPress auf die Suchseite um,
 * und die zeigt hier Treffer aus allen Inhaltstypen in Gruppen (search.php).
 * Wer im Blog sucht, will aber im Blog bleiben — mit Kategorie, Blätterlinks
 * und allem. Denselben Parameternamen wie die Rezept- und Planarchive, damit
 * eine Suche über die Archive hinweg gleich heißt.
 *
 * @return string Getrimmt, '' wenn nicht gesucht wird.
 */
function mmm_blog_search_term(): string {

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- öffentlicher Lesefilter.
	return isset( $_GET['mmm_s'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['mmm_s'] ) ) ) : '';
}

add_action( 'pre_get_posts', 'mmm_blog_apply_search' );

/**
 * Den Suchbegriff in die Archivabfrage legen.
 *
 * `set( 's', … )` nach `parse_query`: die Abfrage sucht damit, aber
 * `is_search()` bleibt falsch — sonst läge die Vorlagenwahl wieder bei
 * search.php, und die Kategorie der Seite wäre verloren.
 *
 * @param WP_Query $query Die laufende Abfrage.
 * @return void
 */
function mmm_blog_apply_search( WP_Query $query ): void {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	/*
	 * Am Query-Objekt geprüft, nicht an den globalen Conditional Tags: in
	 * pre_get_posts beschreiben die zwar die Hauptabfrage, aber eben nur sie —
	 * und ein Hook, der sich auf Globales verlässt, ist der, der beim nächsten
	 * Nebenabfrage-Fall still das Falsche filtert.
	 */
	$ist_blogarchiv = $query->is_home()
		|| $query->is_category()
		|| $query->is_tag()
		|| ( ( $query->is_date() || $query->is_author() ) && ! $query->is_post_type_archive() );

	if ( ! $ist_blogarchiv ) {
		return;
	}

	$term = mmm_blog_search_term();

	if ( '' !== $term ) {
		$query->set( 's', $term );
	}
}

/**
 * Adresse des Archivs ohne Suche — für „Alle" und den Zurücksetzen-Link.
 *
 * Bleibt auf der Kategorie, wenn man in einer steht: eine Suche zurücknehmen
 * heißt nicht, die Kategorie auch zu verlassen.
 *
 * @return string
 */
function mmm_blog_archive_url(): string {

	if ( is_category() ) {
		return (string) get_category_link( (int) get_queried_object_id() );
	}

	if ( is_tag() ) {
		return (string) get_tag_link( (int) get_queried_object_id() );
	}

	if ( is_author() ) {
		return (string) get_author_posts_url( (int) get_queried_object_id() );
	}

	$blog_page = (int) get_option( 'page_for_posts' );

	return $blog_page > 0 ? (string) get_permalink( $blog_page ) : home_url( '/' );
}

/**
 * Ist das hier ein Beitragsarchiv?
 *
 * Entscheidet, ob archive.php dieses Theme oder Astras Vorlage benutzt. Die
 * Template-Hierarchie schickt auch Rezept-Kategorien und -Schlagwörter durch
 * archive.php, und die sollen bleiben, wo sie sind — ihr Kopf, ihre Filter und
 * ihre Karten hängen an Astras Archiv.
 *
 * @return bool
 */
function mmm_blog_is_post_archive(): bool {

	if ( is_home() ) {
		return true;
	}

	if ( is_category() || is_tag() ) {
		return true;
	}

	// Datum und Autor listen in WordPress von Haus aus Beiträge.
	return ( is_date() || is_author() ) && ! is_post_type_archive();
}

/**
 * Überschrift des Beitragsarchivs.
 *
 * Nicht `get_the_archive_title()`: das stellt der Überschrift ihre Gattung
 * voran („Kategorie: Haferflocken"), was auf einer Seite, die ohnehin nur
 * Kategorien auflistet, jede Überschrift gleich anfangen lässt.
 *
 * @return string
 */
function mmm_blog_archive_title(): string {

	if ( is_category() ) {
		return (string) single_cat_title( '', false );
	}

	if ( is_tag() ) {
		return (string) single_tag_title( '', false );
	}

	if ( is_author() ) {
		return (string) get_the_author();
	}

	if ( is_date() ) {
		return (string) get_the_archive_title();
	}

	/*
	 * Die Beitragsseite trägt ihren eigenen Titel, wenn eine Seite dafür
	 * eingestellt ist — sonst das Wort, unter dem der Bereich im Menü steht.
	 */
	$blog_page = (int) get_option( 'page_for_posts' );

	return $blog_page > 0
		? (string) get_the_title( $blog_page )
		: __( 'Blog', 'mixmymeals-hello-child' );
}

/**
 * Beschreibung unter der Archivüberschrift: Term-Beschreibung, Autoren-Bio
 * oder der Inhalt der Beitragsseite.
 *
 * @return string HTML oder ''.
 */
function mmm_blog_archive_description(): string {

	$eigene = (string) get_option( MMM_BLOG_DESCRIPTION_OPTION, '' );

	/*
	 * Eine Kategorie mit eigener Beschreibung behält sie, sonst steht überall
	 * die allgemeine — dieselbe Reihenfolge wie bei den Rezept- und
	 * Plan-Taxonomien (mmm_print_archive_description_from_option()).
	 */
	if ( is_category() || is_tag() ) {

		$term = trim( (string) term_description() );

		return '' !== $term ? $term : wpautop( $eigene );
	}

	if ( is_author() ) {

		$bio = trim( (string) get_the_author_meta( 'description' ) );

		return '' !== $bio ? wpautop( $bio ) : wpautop( $eigene );
	}

	if ( is_home() ) {

		if ( '' !== trim( $eigene ) ) {
			return wpautop( $eigene );
		}

		// Ohne eigene Beschreibung der Inhalt der Beitragsseite, falls eine
		// eingestellt ist: wer dort etwas geschrieben hat, meinte den Blog.
		$blog_page = (int) get_option( 'page_for_posts' );
		$content   = $blog_page > 0 ? trim( (string) get_post_field( 'post_content', $blog_page ) ) : '';

		return '' !== $content ? apply_filters( 'the_content', $content ) : '';
	}

	return wpautop( $eigene );
}

/*
==========================================================
	Blöcke — dieselbe Registry wie Rezept- und Planarchiv
	========================================================== */

add_filter( 'mmm_single_section_types', 'mmm_blog_section_types' );

/**
 * Das Blogarchiv als sortierbaren Typ anmelden.
 *
 * Damit steht es unter Design → Theme-Einstellungen neben den anderen
 * Archiven: Reihenfolge der Blöcke per Ziehen, jeder einzeln abschaltbar.
 * Angemeldet von hier und nicht aus archive-sections.php, weil alles, was
 * diese Blöcke ausgibt, hier steht — die Registry ist dafür ein Filter.
 *
 * @param array<string, string> $types Bisherige Typen.
 * @return array<string, string>
 */
function mmm_blog_section_types( array $types ): array {

	$types['post_archive'] = __( 'Blog archive', 'mixmymeals-hello-child' );

	return $types;
}

add_filter( 'mmm_single_section_registry', 'mmm_blog_section_registry', 10, 2 );

/**
 * Die Blöcke des Blogarchivs.
 *
 * `hero` heißt hier: steht im Kopf über den Karten, solange der Block vor
 * allen anderen einsortiert ist — dieselbe Regel wie bei den übrigen
 * Archiven.
 *
 * @param array  $sections Bisherige Registry.
 * @param string $type     Seitentyp.
 * @return array
 */
function mmm_blog_section_registry( array $sections, string $type ): array {

	if ( 'post_archive' !== $type ) {
		return $sections;
	}

	return array(
		'description' => array(
			'label'  => __( 'Archive description', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_description',
			'hero'   => true,
		),
		'search'      => array(
			'label'  => __( 'Search form', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_search',
			'hero'   => true,
		),
		'categories'  => array(
			'label'  => __( 'Category filter', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_categories',
			'hero'   => true,
		),
		'browse'      => array(
			'label'  => __( 'Browse rows', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_browse',
		),
		'terms'       => array(
			'label'  => __( 'All categories card', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_terms',
		),
		'tags'        => array(
			'label'  => __( 'Tag cloud', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_tags',
		),
		'heading'     => array(
			'label'  => __( 'List heading', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_heading',
		),
		'list'        => array(
			'label'  => __( 'The list', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_list',
		),
		'pagination'  => array(
			'label'  => __( 'Pagination', 'mixmymeals-hello-child' ),
			'render' => 'mmm_blog_section_pagination',
		),
	);
}

/**
 * Beschreibung: Term-Text, Autoren-Bio oder der Inhalt der Beitragsseite.
 */
function mmm_blog_section_description(): void {

	$description = mmm_blog_archive_description();

	if ( '' === trim( $description ) ) {
		return;
	}

	/*
	 * Dieselbe Hülle wie über den anderen Archiven
	 * (mmm_print_archive_description_from_option()), damit der Absatz dort
	 * dieselbe Größe und Farbe hat und nicht als Vorspann gelesen wird.
	 */
	echo '<div class="mmm-archive-description mmm-archive-description-post">'
		. wp_kses_post( $description ) . '</div>';
}

/**
 * Das Suchfeld. Behält Kategorie oder Schlagwort bei: gesucht wird in dem
 * Ausschnitt, in dem man steht, nicht plötzlich im ganzen Blog.
 */
function mmm_blog_section_search(): void {

	$term = mmm_blog_search_term();
	?>
	<form class="mmm-archive-search" role="search" method="get" action="<?php echo esc_url( mmm_blog_archive_url() ); ?>">
		<label class="screen-reader-text" for="mmm-blog-s"><?php esc_html_e( 'Search posts', 'mixmymeals-hello-child' ); ?></label>

		<input class="mmm-archive-search__text" type="search" id="mmm-blog-s" name="mmm_s"
			value="<?php echo esc_attr( $term ); ?>"
			placeholder="<?php esc_attr_e( 'Search posts…', 'mixmymeals-hello-child' ); ?>">

		<button class="mmm-archive-search__submit mmm-btn mmm-btn--primary mmm-btn--sm" type="submit">
			<?php esc_html_e( 'Search', 'mixmymeals-hello-child' ); ?>
		</button>

		<?php if ( '' !== $term ) : ?>
			<a class="mmm-archive-search__reset" href="<?php echo esc_url( mmm_blog_archive_url() ); ?>">
				<?php esc_html_e( 'Reset', 'mixmymeals-hello-child' ); ?>
			</a>
		<?php endif; ?>
	</form>
	<?php
}

/**
 * Die Kategorien als Pillen.
 *
 * Links auf die Kategorie-Archive, keine Filter-Parameter: das Archiv einer
 * Kategorie gibt es in WordPress bereits, es hat eine eigene Adresse, einen
 * Titel und eine Beschreibung. Ein zweiter Weg zu derselben Liste wäre eine
 * Adresse mehr für denselben Inhalt.
 *
 * Leere Kategorien bleiben weg, und bei nur einer einzigen entfällt die Zeile:
 * eine Pille, die man nicht abwählen kann, ist ein Etikett, kein Filter.
 */
function mmm_blog_section_categories(): void {

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || count( $terms ) < 2 ) {
		return;
	}

	$current   = is_category() ? (int) get_queried_object_id() : 0;
	$blog_page = (int) get_option( 'page_for_posts' );
	$all_url   = $blog_page > 0 ? (string) get_permalink( $blog_page ) : home_url( '/' );
	?>
	<nav class="mmm-term-switcher meal-plan-switcher" aria-label="<?php esc_attr_e( 'Categories', 'mixmymeals-hello-child' ); ?>">
		<a class="<?php echo 0 === $current ? 'active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>">
			<?php esc_html_e( 'All', 'mixmymeals-hello-child' ); ?>
		</a>
		<?php foreach ( $terms as $term ) : ?>
			<a class="<?php echo $current === (int) $term->term_id ? 'active' : ''; ?>"
				href="<?php echo esc_url( (string) get_category_link( $term ) ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Wie viele Beiträge eine Stöber-Reihe zeigt.
 */
const MMM_BLOG_ROW_POSTS = 8;

/**
 * Wie viele Kategorien eine eigene Reihe bekommen.
 *
 * Drei, dazu die neuesten Beiträge: dieselbe Zahl Reihen wie über dem
 * Rezeptarchiv. Mehr wäre kein Stöbern mehr, sondern eine zweite Liste vor
 * der Liste.
 */
const MMM_BLOG_ROW_CATEGORIES = 3;

/**
 * Eine Stöber-Reihe: Überschrift, Notiz, waagerecht scrollende Karten.
 *
 * Dasselbe Markup wie die Reihen über dem Rezeptarchiv — bis auf die Karten
 * dieselben Klassen, damit beide Seiten dieselbe Bauform zeigen und nicht
 * zwei Karussells, die sich ähneln. Die Klassen heißen nach dem Rezept, weil
 * sie dort entstanden sind; eine zweite Kopie unter neuem Namen wäre die
 * Stelle, an der die beiden auseinanderlaufen.
 *
 * @param array  $posts Beiträge.
 * @param string $title Überschrift.
 * @param string $note  Zeile darunter, '' für keine.
 * @param string $url   Ziel der Überschrift und des „Alle anzeigen"-Links.
 * @param int    $total Wie viele es insgesamt gibt (für den Link).
 * @param bool   $boxed Mit Kasten (Archiv) oder ohne (im Seiteninhalt).
 * @return void
 */
function mmm_blog_render_row( array $posts, string $title, string $note, string $url = '', int $total = 0, bool $boxed = true ): void {

	if ( ! $posts ) {
		return;
	}

	/*
	 * Der Link entfällt, wenn die Reihe schon alles trägt, was es gibt: „Alle
	 * 5 anzeigen" unter fünf sichtbaren Karten führt auf eine Seite, die
	 * dasselbe zeigt.
	 */
	$mehr = '' !== $url && $total > count( $posts );

	/*
	 * Der Kasten gehört der Seite, nicht der Reihe. Im Archiv steht sie auf
	 * dem Seitenhintergrund und braucht ihn, damit sie als Block zu erkennen
	 * ist; im Inhalt einer Seite steht sie bereits in einer Karte, und ein
	 * zweiter Rahmen darin ist nur noch Rand.
	 */
	$klassen = 'mmm-blog-browse' . ( $boxed ? ' mmm-recipe-browse' : ' mmm-blog-browse--plain' );
	?>
	<section class="<?php echo esc_attr( $klassen ); ?>">

		<?php if ( '' !== $title || $mehr ) : ?>
		<div class="mmm-recipe-row__header">
			<?php if ( '' !== $title ) : ?>
			<h2 class="mmm-recipe-row__title">
				<?php if ( '' !== $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $title ); ?>
				<?php endif; ?>
			</h2>
			<?php endif; ?>

			<?php if ( $mehr ) : ?>
				<a class="mmm-recipe-row__more" href="<?php echo esc_url( $url ); ?>">
					<?php
					printf(
						/* translators: %s: number of posts, already formatted. */
						esc_html( _n( 'Show all %s', 'Show all %s', $total, 'mixmymeals-hello-child' ) ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( '' !== trim( $note ) ) : ?>
			<p class="mmm-browse-note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>

		<div class="mmm-recipe-row">
			<div class="mmm-recipe-row__track">
				<?php foreach ( $posts as $post ) : ?>
					<?php $permalink = (string) get_permalink( $post ); ?>
					<article class="mmm-recipe-row__card<?php echo has_post_thumbnail( $post ) ? '' : ' mmm-recipe-row__card--noimg'; ?>">
						<?php if ( has_post_thumbnail( $post ) ) : ?>
							<a class="mmm-recipe-row__media" href="<?php echo esc_url( $permalink ); ?>">
								<?php echo get_the_post_thumbnail( $post, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
						<div class="mmm-recipe-row__body">
							<?php /*
							Ohne Chips: die Rezeptreihen lassen ihre Meta-Pillen
									aus demselben Grund weg — auf 210px Breite ist
									jede Zeile neben dem Titel eine zu viel. */ ?>
							<h3 class="mmm-recipe-row__name">
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
							</h3>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>

	</section>
	<?php
}

add_shortcode( 'mmm_blog_posts', 'mmm_blog_posts_shortcode' );

/**
 * Shortcode [mmm_blog_posts]: Beiträge einer Kategorie, als Reihe oder Raster.
 *
 * Dieselbe Reihe wie über dem Blogarchiv, nur überall einsetzbar — Startseite,
 * Landingpage, Seiteninhalt. Ein Shortcode und keine Funktion, weil genau das
 * der Unterschied ist: der Aufrufer ist hier der Editor und keine Vorlage.
 *
 * Attribute:
 *   category  Slug oder ID. Leer = alle Beiträge.
 *   count     Wie viele, Vorgabe 8.
 *   title     Überschrift. Vorgabe: Name der Kategorie, ohne Kategorie
 *             „Neueste Beiträge". title="" lässt sie weg.
 *   note      Zeile darunter. Vorgabe: Beschreibung der Kategorie.
 *   layout    row (Vorgabe, waagerecht scrollend) oder grid (Karten wie im
 *             Archiv).
 *   link      1 (Vorgabe) zeigt „Alle N anzeigen", sobald es mehr gibt, als
 *             die Reihe trägt.
 *   boxed     0 (Vorgabe) ohne eigenen Rahmen — im Seiteninhalt steht die
 *             Reihe schon in einer Karte. boxed="1" setzt sie in einen
 *             eigenen Kasten wie im Archiv.
 *
 * Eine genannte, aber unbekannte Kategorie gibt '' zurück statt stillschweigend
 * alle Beiträge: ein Tippfehler im Slug soll auffallen und nicht die falsche
 * Liste zeigen.
 *
 * @param array|string $atts Attribute.
 * @return string
 */
function mmm_blog_posts_shortcode( $atts = array() ): string {

	$atts = shortcode_atts(
		array(
			'category' => '',
			'count'    => 8,
			'title'    => null,
			'note'     => null,
			'layout'   => 'row',
			'link'     => '1',
			'boxed'    => '0',
		),
		$atts,
		'mmm_blog_posts'
	);

	$count = max( 1, (int) $atts['count'] );
	$term  = null;

	if ( '' !== trim( (string) $atts['category'] ) ) {

		$wanted = trim( (string) $atts['category'] );
		$term   = is_numeric( $wanted )
			? get_term( (int) $wanted, 'category' )
			: get_term_by( 'slug', sanitize_title( $wanted ), 'category' );

		if ( ! $term instanceof WP_Term ) {
			return '';
		}
	}

	$query = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
	);

	if ( $term instanceof WP_Term ) {
		$query['cat'] = (int) $term->term_id;
	}

	$posts = get_posts( $query );

	if ( ! $posts ) {
		return '';
	}

	// null (nicht gesetzt) heißt Vorgabe, '' heißt ausdrücklich keine.
	$title = null === $atts['title']
		? ( $term instanceof WP_Term ? $term->name : __( 'Latest posts', 'mixmymeals-hello-child' ) )
		: (string) $atts['title'];

	$note = null === $atts['note']
		? ( $term instanceof WP_Term ? (string) $term->description : '' )
		: (string) $atts['note'];

	$url   = ( $term instanceof WP_Term && filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN ) )
		? (string) get_category_link( $term )
		: '';
	$total = $term instanceof WP_Term ? (int) $term->count : 0;

	$boxed = filter_var( $atts['boxed'], FILTER_VALIDATE_BOOLEAN );

	ob_start();

	if ( 'grid' === $atts['layout'] ) {
		mmm_blog_render_grid_section( $posts, $title, $note, $url, $total, $boxed );
	} else {
		mmm_blog_render_row( $posts, $title, $note, $url, $total, $boxed );
	}

	return (string) ob_get_clean();
}

/**
 * Dieselbe Sektion wie die Reihe, nur mit dem Kartenraster des Archivs.
 *
 * Für Stellen, an denen waagerechtes Scrollen nichts taugt — eine Seite, die
 * ohnehin untereinander gelesen wird, oder eine Spalte, die für die 210px
 * breiten Reihenkarten zu schmal ist.
 *
 * @param WP_Post[] $posts Beiträge.
 * @param string    $title Überschrift.
 * @param string    $note  Zeile darunter.
 * @param string    $url   Ziel der Überschrift und des Links.
 * @param int       $total Wie viele es insgesamt gibt.
 * @param bool      $boxed Mit Kasten (Archiv) oder ohne (im Seiteninhalt).
 * @return void
 */
function mmm_blog_render_grid_section( array $posts, string $title, string $note, string $url = '', int $total = 0, bool $boxed = true ): void {

	if ( ! $posts ) {
		return;
	}

	$mehr    = '' !== $url && $total > count( $posts );
	$klassen = 'mmm-blog-browse mmm-blog-browse--grid'
		. ( $boxed ? ' mmm-recipe-browse' : ' mmm-blog-browse--plain' );
	?>
	<section class="<?php echo esc_attr( $klassen ); ?>">

		<?php if ( '' !== $title || $mehr ) : ?>
			<div class="mmm-recipe-row__header">
				<?php if ( '' !== $title ) : ?>
					<h2 class="mmm-recipe-row__title">
						<?php if ( '' !== $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $title ); ?>
						<?php endif; ?>
					</h2>
				<?php endif; ?>

				<?php if ( $mehr ) : ?>
					<a class="mmm-recipe-row__more" href="<?php echo esc_url( $url ); ?>">
						<?php
						printf(
							/* translators: %s: number of posts, already formatted. */
							esc_html( _n( 'Show all %s', 'Show all %s', $total, 'mixmymeals-hello-child' ) ),
							esc_html( number_format_i18n( $total ) )
						);
						?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( '' !== trim( $note ) ) : ?>
			<p class="mmm-browse-note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>

		<div class="mmm-post-grid">
			<?php foreach ( $posts as $post ) : ?>
				<?php mmm_blog_render_card( $post ); ?>
			<?php endforeach; ?>
		</div>

	</section>
	<?php
}

/**
 * Die Stöber-Reihen: zuletzt erschienen, dann je Kategorie eine.
 *
 * Nur auf der Beitragsseite, nur auf Seite eins und nur ohne Suche —
 * dieselben drei Bedingungen wie über dem Rezeptarchiv, aus denselben
 * Gründen: wer blättert, arbeitet die Liste ab, und wer sucht, hat eine
 * Frage gestellt, vor die keine Vorschläge gehören. Auf einem Kategorie-
 * oder Schlagwortarchiv entfallen sie ebenfalls, dort wäre die erste Reihe
 * die Liste darunter.
 */
function mmm_blog_section_browse(): void {

	if ( ! is_home() || is_paged() || '' !== mmm_blog_search_term() ) {
		return;
	}

	$neueste = get_posts(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => MMM_BLOG_ROW_POSTS,
			'ignore_sticky_posts' => true,
		)
	);

	mmm_blog_render_row(
		$neueste,
		__( 'Latest posts', 'mixmymeals-hello-child' ),
		__( 'The most recently published posts — everything new lands here.', 'mixmymeals-hello-child' )
	);

	$kategorien = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => MMM_BLOG_ROW_CATEGORIES,
		)
	);

	if ( is_wp_error( $kategorien ) ) {
		return;
	}

	foreach ( $kategorien as $kategorie ) {

		$posts = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => MMM_BLOG_ROW_POSTS,
				'cat'                 => (int) $kategorie->term_id,
				'ignore_sticky_posts' => true,
			)
		);

		mmm_blog_render_row(
			$posts,
			$kategorie->name,
			(string) $kategorie->description,
			(string) get_category_link( $kategorie ),
			(int) $kategorie->count
		);
	}
}

/**
 * Die Karte „Alle Kategorien".
 *
 * Das Gegenstück zur gleichnamigen Karte unter den Rezeptreihen, mit
 * denselben Klassen. Jede Kategorie mit ihrer Zahl — die Zahl ist der
 * Unterschied zwischen einer Liste von Wörtern und einer Auskunft darüber,
 * wo etwas zu holen ist.
 */
function mmm_blog_section_terms(): void {

	if ( ! is_home() || is_paged() || '' !== mmm_blog_search_term() ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $terms ) || count( $terms ) < 2 ) {
		return;
	}
	?>
	<section id="mmm-all-categories" class="mmm-archive-terms mmm-blog-terms">
		<h2 class="mmm-archive-terms__title"><?php esc_html_e( 'All categories', 'mixmymeals-hello-child' ); ?></h2>
		<p class="mmm-browse-note"><?php esc_html_e( 'Every heading the blog is sorted under.', 'mixmymeals-hello-child' ); ?></p>
		<?php mmm_blog_render_term_list( 0 ); ?>
	</section>
	<?php
}

/**
 * Die Kategorienliste, wie sie das Rezeptarchiv zeigt.
 *
 * Dieselben Klassen wie [mmm_recipe_category_terms] (`mmm-recipe-category
 * mmm-term-list --level-N`), also auch dieselbe Darstellung: oberste Ebene als
 * nebeneinander umbrechende Spalten, Unterkategorien darunter gestapelt. Vorher
 * standen hier Pillen — dieselbe Information, aber eine andere Bauform als
 * zwei Bildschirme weiter, und genau darum ging es.
 *
 * Auch die Beitragszahl hinter dem Namen ist weg: das Rezeptarchiv ruft die
 * Liste ohne sie auf, und die kleinere, hellere Zahl war das Einzige, was die
 * beiden Listen noch unterschiedlich aussehen ließ. Verlinkt bleibt die
 * oberste Ebene aber — anders als bei Rezepten (`top_level_link=0`) ist eine
 * Blog-Kategorie selbst ein Archiv, auf das man gehen will.
 *
 * Kategorien sind in WordPress hierarchisch; die Rekursion ist deshalb keine
 * Vorsorge, sondern der Normalfall, sobald jemand eine Unterkategorie anlegt.
 *
 * @param int $parent Elternkategorie, 0 für die oberste Ebene.
 * @param int $level  Tiefe, 1-basiert wie im Plugin.
 * @return void
 */
function mmm_blog_render_term_list( int $parent = 0, int $level = 1 ): void {

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'parent'     => $parent,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	?>
	<ul class="mmm-recipe-category mmm-term-list mmm-term-list--level-<?php echo (int) $level; ?>">
		<?php foreach ( $terms as $term ) : ?>
			<li class="mmm-term-list__item mmm-term-list__item--term-<?php echo (int) $term->term_id; ?>">
				<a class="mmm-term-list__link" href="<?php echo esc_url( (string) get_category_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
				<?php mmm_blog_render_term_list( (int) $term->term_id, $level + 1 ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Überschrift über der Liste, mit der Zahl der Treffer.
 *
 * Über einer gefilterten Liste wäre „Alle Beiträge" gelogen, also sagt die
 * Überschrift, was tatsächlich darunter steht — dieselbe Entscheidung wie im
 * Rezeptarchiv.
 *
 * Sie steht über jeder Liste, auch auf Kategorie- und Schlagwortarchiven. Dort
 * wiederholt sie zwar nicht den Namen aus der H1, trennt aber die Liste von
 * dem, was darüber steht — und ohne sie endete die Seite in einem Raster ohne
 * Anfang.
 */
function mmm_blog_section_heading(): void {

	global $wp_query;

	$term  = mmm_blog_search_term();
	$found = (int) $wp_query->found_posts;

	if ( ! have_posts() ) {
		return;
	}
	?>
	<h2 class="mmm-recipe-archive-heading mmm-blog-archive__heading">
		<?php
		if ( '' !== $term ) {
			printf(
				/* translators: %s: the search term. */
				esc_html__( 'Search results for “%s”', 'mixmymeals-hello-child' ),
				esc_html( $term )
			);
		} else {
			esc_html_e( 'All posts', 'mixmymeals-hello-child' );
		}
		?>
		<span class="mmm-blog-archive__count"><?php echo esc_html( number_format_i18n( $found ) ); ?></span>
	</h2>
	<?php
}

/**
 * Die Karten — oder der Leer-Zustand, wenn nichts passt.
 */
function mmm_blog_section_list(): void {

	if ( have_posts() ) {
		echo '<div class="mmm-post-grid">';

		while ( have_posts() ) {
			the_post();
			mmm_blog_render_card();
		}

		echo '</div>';

		return;
	}

	mmm_blog_render_empty();
}

/**
 * Die Schlagwörter des Blogs, unter der Liste.
 *
 * Bei den Stöber-Blöcken und nicht im Kopf: Schlagwörter sind viele und klein,
 * sie führen weiter, statt die Auswahl davor zu treffen — die Kategorien oben
 * tun das schon. Dieselbe Bedingung wie die Reihen darüber: nur auf der
 * Beitragsseite, nur auf Seite eins, nur ohne Suche.
 */
function mmm_blog_section_tags(): void {

	if ( ! is_home() || is_paged() || '' !== mmm_blog_search_term() ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'number'     => 24,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	$current = is_tag() ? (int) get_queried_object_id() : 0;
	?>
	<section class="mmm-recipe-section mmm-blog-tags">
		<div class="mmm-section-head">
			<h2><?php esc_html_e( 'Tags', 'mixmymeals-hello-child' ); ?></h2>
			<span class="mmm-section-head__line"></span>
		</div>
		<p class="mmm-post-tags__list">
			<?php foreach ( $terms as $term ) : ?>
				<a class="mmm-chip<?php echo $current === (int) $term->term_id ? ' mmm-chip--active' : ''; ?>"
					href="<?php echo esc_url( (string) get_tag_link( $term ) ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endforeach; ?>
		</p>
	</section>
	<?php
}

/**
 * Blätterlinks.
 */
function mmm_blog_section_pagination(): void {

	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'class'     => 'mmm-post-pagination',
			'prev_text' => esc_html__( 'Newer', 'mixmymeals-hello-child' ),
			'next_text' => esc_html__( 'Older', 'mixmymeals-hello-child' ),
		)
	);
}

/**
 * Nichts gefunden.
 *
 * Mit Suchbegriff ein anderer Text als ohne: „noch keine Beiträge" ist falsch,
 * wenn es welche gibt und nur die Suche nichts trifft.
 */
function mmm_blog_render_empty(): void {

	$term = mmm_blog_search_term();
	?>
	<div class="mmm-archive-empty">
		<?php if ( '' !== $term ) : ?>
			<p class="mmm-archive-empty__title"><?php esc_html_e( 'Nothing matched that.', 'mixmymeals-hello-child' ); ?></p>
			<p class="mmm-archive-empty__text">
				<?php esc_html_e( 'Maybe a different word — or have a look at everything that is here:', 'mixmymeals-hello-child' ); ?>
			</p>
			<p class="mmm-archive-empty__actions">
				<a class="mmm-btn mmm-btn--ghost mmm-archive-empty__reset" href="<?php echo esc_url( mmm_blog_archive_url() ); ?>">
					<?php esc_html_e( 'Reset search', 'mixmymeals-hello-child' ); ?>
				</a>
			</p>
		<?php else : ?>
			<p class="mmm-archive-empty__title"><?php esc_html_e( 'Nothing here yet.', 'mixmymeals-hello-child' ); ?></p>
			<p class="mmm-archive-empty__text">
				<?php esc_html_e( 'No posts under this heading. There is cooking elsewhere:', 'mixmymeals-hello-child' ); ?>
			</p>
			<p class="mmm-archive-empty__actions">
				<a class="mmm-btn mmm-btn--ghost" href="<?php echo esc_url( (string) get_post_type_archive_link( 'mmm_recipe' ) ); ?>">
					<?php esc_html_e( 'To the recipes', 'mixmymeals-hello-child' ); ?>
				</a>
				<a class="mmm-btn mmm-btn--ghost" href="<?php echo esc_url( (string) get_post_type_archive_link( 'mmm_meal_plan' ) ); ?>">
					<?php esc_html_e( 'To the meal plans', 'mixmymeals-hello-child' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Das Beitragsarchiv: Kopf, Karten, Blätterlinks.
 *
 * Eine Funktion für drei Vorlagen (home.php, archive.php und, über die beiden,
 * Kategorie-, Schlagwort-, Datums- und Autorenarchive). Getrennte Dateien
 * wären drei Kopien derselben zwanzig Zeilen, und die erste Änderung hätte
 * zwei davon vergessen.
 *
 * Welche Blöcke in welcher Reihenfolge erscheinen, steht unter Design →
 * Theme-Einstellungen — dieselbe Mechanik wie beim Rezept- und Planarchiv.
 * Die Blöcke, die in den Kopf gehören (`hero`), stehen dort, solange sie vorn
 * einsortiert sind; ab dem ersten, der das nicht ist, läuft alles unter den
 * Karten weiter.
 *
 * @return void
 */
function mmm_blog_render_archive(): void {

	$slots = mmm_archive_section_slots( 'post_archive' );
	?>
	<?php
	/*
	 * Ohne .mmm-plan-page. Das ist das Seitenmaß der Einzelansichten (Breite,
	 * 32 px oben, 64 px unten) — die Archive der anderen Inhaltstypen tragen
	 * es nicht, sie liegen direkt in Astras Container. Mit beidem stand die
	 * Blog-Überschrift genau diese 32 px tiefer als „Alle Rezepte".
	 */
	?>
	<section class="mmm-blog-archive">

		<?php
		/*
		 * Astras eigene Archivkopf-Klassen, nicht nachgebaute: Polsterung und
		 * Abstände dieses Kastens kommen aus Astras dynamischem CSS, und zwar
		 * je Inhaltstyp aus den Banner-Einstellungen des Customizers. Vorher
		 * stand hier eine fest verdrahtete Zahl, die ich vom Rezeptarchiv
		 * abgemessen hatte — richtig für Rezepte, geraten für Beiträge.
		 *
		 * mmm-plan-hero bleibt daneben stehen: der grüne Schimmer und die
		 * Titeltypografie hängen an einer Regel, die .ast-archive-description
		 * nur unter `.archive` greift — auf der Beitragsseite trägt der Body
		 * aber `home blog`.
		 */
		?>
		<section class="mmm-l-archive-description mmm-plan-hero mmm-blog-archive__head">
			<h1 class="page-title mmm-l-archive-title"><?php echo esc_html( mmm_blog_archive_title() ); ?></h1>
			<?php mmm_render_archive_section_list( 'post_archive', $slots['hero'] ); ?>
		</section>

		<?php mmm_render_archive_section_list( 'post_archive', $slots['flow'] ); ?>

	</section>
	<?php
}
