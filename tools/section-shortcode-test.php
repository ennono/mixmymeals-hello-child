<?php
/**
 * Prüft [mmm_section] ohne WordPress.
 *
 * Der Shortcode ist der Weg, auf dem die Inhalte zu Rezepten, Plänen und
 * Archiven in eine Elementor-Vorlage kommen — er muss den Seitentyp
 * erkennen, den richtigen Block aus der Registry holen und sich bei einem
 * Tippfehler gegenüber Besuchern still verhalten.
 *
 * Die Funktionen von WordPress und des Themes sind hier als Attrappen
 * nachgebildet, die Registry mit zwei erfundenen Seitentypen. Geprüft wird
 * also die Logik des Shortcodes, nicht das Zusammenspiel mit der echten
 * Installation.
 *
 *   php tools/section-shortcode-test.php
 *
 * @package MixMyMeals Hello Child
 */

// Attrappen der WordPress- und Theme-Funktionen, die [mmm_section] berührt.
$GLOBALS['sc'] = array();
$GLOBALS['caps'] = true;
$GLOBALS['singular'] = '';
$GLOBALS['calls'] = array();
$GLOBALS['post_id'] = 0;

function add_shortcode( $tag, $cb ) { $GLOBALS['sc'][ $tag ] = $cb; }
function do_sc( $tag, $atts = array() ) { return call_user_func( $GLOBALS['sc'][ $tag ], $atts ); }
function shortcode_atts( $pairs, $atts, $tag = '' ) { return array_merge( $pairs, (array) $atts ); }
function sanitize_key( $k ) { return strtolower( preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $k ) ); }
function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); }
function esc_url( $u ) { return $u; }
function esc_attr( $a ) { return htmlspecialchars( (string) $a, ENT_QUOTES ); }
function __( $t, $d = '' ) { return $t; }
function _x( $t, $c, $d = '' ) { return $t; }
function class_exists_stub() {}
function current_user_can( $c ) { return $GLOBALS['caps']; }
function is_singular( $pt = '' ) { return $GLOBALS['singular'] === $pt; }
function apply_filters( $tag, $value ) { return $value; }
function home_url( $p = '' ) { return 'https://example.test' . $p; }

function get_the_ID() { return $GLOBALS['post_id'] ?? 0; }
function get_template_part( $slug, $name = null ) { echo '<article>KARTE ' . $GLOBALS['post_id'] . '</article>'; }
function mmm_archive_section_type() { return $GLOBALS['singular'] === 'archive_plan' ? 'plan_archive' : ''; }
function mmm_single_section_registry( $type ) {
    $r = array(
        'recipe' => array(
            'steps'       => array( 'label' => 'Zubereitung', 'render' => function () { echo '<section>ZUBEREITUNG</section>'; } ),
            'ingredients' => array( 'label' => 'Zutaten',     'render' => function () { echo '<section>ZUTATEN</section>'; } ),
        ),
        'plan_archive' => array(
            'list' => array( 'label' => 'Liste', 'render' => function () { echo '<div>PLANLISTE</div>'; } ),
        ),
    );
    return $r[ $type ] ?? array();
}
function mmm_render_single_sections( $type ) {
    $GLOBALS['calls'][] = "render_all:$type";
    foreach ( mmm_single_section_registry( $type ) as $slug => $s ) { call_user_func( $s['render'] ); }
}

// Die echte Datei laden — nur den Teil ohne class_exists-Wächter geht nicht,
// also den Wächter erfüllen.
class mixmymeals {}
require __DIR__ . '/../includes/shortcodes.php';

$ok = 0; $fail = 0;
function check( $name, $cond, $got = '' ) {
    global $ok, $fail;
    if ( $cond ) { $ok++; echo "  ok   $name\n"; }
    else { $fail++; echo "  FAIL $name" . ( $got !== '' ? "  -> $got" : '' ) . "\n"; }
}

echo "1. Ein Block, Typ ausdrücklich\n";
$out = do_sc( 'mmm_section', array( 'type' => 'recipe', 'block' => 'steps' ) );
check( 'gibt die Zubereitung aus', strpos( $out, 'ZUBEREITUNG' ) !== false, $out );
check( 'und sonst nichts', strpos( $out, 'ZUTATEN' ) === false );

echo "2. Typ wird aus der Seite erkannt\n";
$GLOBALS['singular'] = 'mmm_recipe';
$out = do_sc( 'mmm_section', array( 'block' => 'ingredients' ) );
check( 'Rezeptseite -> recipe', strpos( $out, 'ZUTATEN' ) !== false, $out );

echo "3. Ohne Block: die ganze Seite\n";
$GLOBALS['calls'] = array();
$out = do_sc( 'mmm_section', array() );
check( 'ruft mmm_render_single_sections', in_array( 'render_all:recipe', $GLOBALS['calls'], true ) );
check( 'enthält beide Abschnitte', strpos( $out, 'ZUBEREITUNG' ) !== false && strpos( $out, 'ZUTATEN' ) !== false );

echo "4. Archiv über dieselbe Registry\n";
$GLOBALS['singular'] = 'archive_plan';
$out = do_sc( 'mmm_section', array( 'block' => 'list' ) );
check( 'Planarchiv -> plan_archive', strpos( $out, 'PLANLISTE' ) !== false, $out );

echo "5. Unbekannter Block\n";
$GLOBALS['singular'] = 'mmm_recipe';
$GLOBALS['caps'] = true;
$out = do_sc( 'mmm_section', array( 'block' => 'gibtsnicht' ) );
check( 'Redakteur bekommt einen Hinweis', strpos( $out, 'Unknown section' ) !== false, $out );
check( 'der Hinweis nennt die gültigen Namen', strpos( $out, 'steps' ) !== false && strpos( $out, 'ingredients' ) !== false );
$GLOBALS['caps'] = false;
$out = do_sc( 'mmm_section', array( 'block' => 'gibtsnicht' ) );
check( 'Besucher bekommt nichts', '' === $out, var_export( $out, true ) );

echo "6. Kein erkennbarer Typ\n";
$GLOBALS['singular'] = ''; $GLOBALS['caps'] = false;
check( 'Besucher bekommt nichts', '' === do_sc( 'mmm_section', array() ) );
$GLOBALS['caps'] = true;
check( 'Redakteur bekommt einen Hinweis', strpos( do_sc( 'mmm_section', array() ), 'No page type' ) !== false );

echo "7. [mmm_card] in der Schleife\n";
$GLOBALS['post_id'] = 7022;
$out = do_sc( 'mmm_card', array() );
check( 'gibt die Karte des Beitrags aus', strpos( $out, 'KARTE 7022' ) !== false, $out );
$GLOBALS['post_id'] = 0;
check( 'außerhalb einer Schleife nichts', '' === do_sc( 'mmm_card', array() ) );

echo "\n$ok ok, $fail fehlgeschlagen\n";
exit( $fail ? 1 : 0 );
