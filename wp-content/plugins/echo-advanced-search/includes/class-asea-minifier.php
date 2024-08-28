<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A class that contains minification methods
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * Some code adapted from code in LiteSpeed Cache (Copyright (c) 2013 Yahoo! Inc.)
 */
class ASEA_Minifier {

	/**
	 * Removes comments, spaces, and line breaks form CSS. Limits single line length. Removes empty rule blocks.
	 *
	 * @param $css
	 * @return string
	 */
	public static function minify_css( $css ) {

		if ( ! is_string( $css ) ) {
			return '';
		}

		// Remove comments
		$css = preg_replace( '/(?<!\\\\)\/\*(.*?)\*(?<!\\\\)\//Ss', '', $css );

		// Process quoted unquotable attribute selectors to unquote them. Covers most common cases.
		// Likelyhood of a quoted attribute selector being a substring in a string: Very very low.
		$css = preg_replace( '/\[\s*([a-z][a-z-]+)\s*([\*\|\^\$~]?=)\s*[\'"](-?[a-z_][a-z0-9-_]+)[\'"]\s*\]/Ssi', '[$1$2$3]', $css );

		// Normalize all whitespace strings to single spaces
		$css = preg_replace('/\s+/S', ' ', $css );

		// Remove spaces before the things that should not have spaces before them.
		$css = preg_replace( '/ ([@{};>+)\]~=,\/\n])/S', '$1', $css );

		// Remove the spaces after the things that should not have spaces after them.
		$css = preg_replace( '/([{}:;>+(\[~=,\/\n]) /S', '$1', $css );

		// Find a fraction that may used in some @media queries such as: (min-aspect-ratio: 1/1)
		// Add token to add the "/" back in later
		$css = preg_replace( '/\(([a-z-]+):([0-9]+)\/([0-9]+)\)/Si', '($1:$2'. '_CSSMIN_QF_' .'$3)', $css );

		// Remove empty rule blocks up to 2 levels deep.
		$css = preg_replace( array_fill( 0, 2, '/(\{)[^{};\/\n]+\{\}/S' ), '$1', $css );
		$css = preg_replace( '/[^{};\/\n]+\{\}/S', '', $css );

		// Restore fraction
		$css = str_replace( '_CSSMIN_QF_', '/', $css );

		// Some source control tools don't like it when files containing lines longer
		// than, say 8000 characters, are checked in. The linebreak option is used in
		// that case to split long lines after a specific column.
		$line_break_position = 5000;
		$l = strlen( $css );
		$offset = $line_break_position;
		while ( preg_match( '/(?<!\\\\)\}(?!\n)/S', $css, $matches, PREG_OFFSET_CAPTURE, $offset ) ) {
			$matchIndex = $matches[0][1];
			$css = substr_replace( $css, "\n", $matchIndex + 1, 0 );
			$offset = $matchIndex + 2 + $line_break_position;
			$l += 1;
			if ( $offset > $l ) {
				break;
			}
		}

		return $css;
	}
}
