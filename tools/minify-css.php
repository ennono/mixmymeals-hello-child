<?php
/**
 * Builds style.min.css from style.css.
 *
 *     php tools/minify-css.php
 *
 * Run after every change to style.css. Forgetting it is harmless: the first
 * line of the output records a hash of the source, and child_enqueue_styles()
 * serves style.css itself whenever that hash no longer matches — a stale
 * minified copy is never delivered.
 *
 * Deliberately conservative: comments go, whitespace collapses, and spaces are
 * dropped only next to { } ; , and after a colon. Nothing is reordered or
 * merged, and + - > ~ keep their spaces (calc() needs them; a space before a
 * colon can be a descendant combinator, ".a :is(.b)"). Strings are copied
 * untouched. The script checks its own result: the token sequence of input
 * and output must match, or it writes nothing.
 *
 * @package MixMyMeals Astra Child
 */

// A build tool, not a page: a copy uploaded with the theme must not run
// when somebody requests its URL.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$dir = dirname( __DIR__ );
$src = file_get_contents( $dir . '/style.css' );

if ( false === $src ) {
	fwrite( STDERR, "style.css not readable\n" );
	exit( 1 );
}

/**
 * Splits CSS into strings, comments, whitespace and the rest.
 *
 * @param string $css Source.
 * @return array<int, array{0:string,1:string}> [type, text].
 */
function mmm_css_tokens( string $css ): array {
	$out = array();
	$len = strlen( $css );
	$i   = 0;
	$buf = '';
	while ( $i < $len ) {
		$c = $css[ $i ];
		if ( '/' === $c && '*' === ( $css[ $i + 1 ] ?? '' ) ) {
			$end   = strpos( $css, '*/', $i + 2 );
			$end   = false === $end ? $len : $end + 2;
			$out[] = array( 'text', $buf );
			$out[] = array( 'comment', substr( $css, $i, $end - $i ) );
			$buf   = '';
			$i     = $end;
			continue;
		}
		if ( '"' === $c || "'" === $c ) {
			$j = $i + 1;
			while ( $j < $len && $css[ $j ] !== $c ) {
				$j += ( '\\' === $css[ $j ] ) ? 2 : 1;
			}
			$out[] = array( 'text', $buf );
			$out[] = array( 'string', substr( $css, $i, $j + 1 - $i ) );
			$buf   = '';
			$i     = $j + 1;
			continue;
		}
		$buf .= $c;
		++$i;
	}
	$out[] = array( 'text', $buf );
	return $out;
}

$min = '';
foreach ( mmm_css_tokens( $src ) as list( $type, $text ) ) {
	if ( 'comment' === $type ) {
		continue;
	}
	if ( 'string' === $type ) {
		$min .= $text;
		continue;
	}
	$text = preg_replace( '/\s+/', ' ', $text );
	$text = preg_replace( '/\s*([{};,])\s*/', '$1', $text );
	$text = preg_replace( '/:\s+/', ':', $text );
	$text = preg_replace( '/\s+!important/', '!important', $text );
	$min .= $text;
}
// The last semicolon of each block. Strings could in theory hold ";}" —
// the self-check below would refuse such a result.
$min = str_replace( ';}', '}', trim( $min ) );

/**
 * What must survive: every non-space token, in order, ignoring the semicolon
 * the minifier drops before a closing brace.
 *
 * @param string $css CSS.
 * @return string[]
 */
function mmm_css_signature( string $css ): array {
	$sig = array();
	foreach ( mmm_css_tokens( $css ) as list( $type, $text ) ) {
		if ( 'comment' === $type ) {
			continue;
		}
		if ( 'string' === $type ) {
			$sig[] = $text;
			continue;
		}
		preg_match_all( '/[{};,:]|![a-z]+|[^\s{};,:!]+/i', $text, $m );
		foreach ( $m[0] as $t ) {
			$sig[] = $t;
		}
	}
	// Drop ";" directly before "}" (and runs of ";").
	$clean = array();
	foreach ( $sig as $t ) {
		if ( '}' === $t ) {
			while ( $clean && ';' === end( $clean ) ) {
				array_pop( $clean );
			}
		}
		$clean[] = $t;
	}
	return $clean;
}

if ( mmm_css_signature( $src ) !== mmm_css_signature( $min ) ) {
	fwrite( STDERR, "Self-check failed: minified CSS differs in content. Nothing written.\n" );
	exit( 1 );
}

$header = '/* src:' . md5( $src ) . " */\n";
file_put_contents( $dir . '/style.min.css', $header . $min . "\n" );

printf(
	"style.min.css: %d → %d bytes (gzip %d → %d)\n",
	strlen( $src ),
	strlen( $header . $min ),
	strlen( gzencode( $src, 9 ) ),
	strlen( gzencode( $header . $min, 9 ) )
);
