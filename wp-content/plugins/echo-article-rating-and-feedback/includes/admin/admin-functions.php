<?php

/*** GENERIC NON-KB functions  ***/

/**
 * Hide post meta from Admin
 * @param $protected
 * @param $meta_key
 * @param $meta_type
 * @return bool
 */
function eprf_hide_custom_field_from_admin( $protected, $meta_key, $meta_type ) {
	$protected_meta = array( 'eprf-article-rating-average', 'eprf-article-rating-dislike', 'eprf-article-rating-like' );
	return in_array( $meta_key, $protected_meta ) ? true : $protected;
}
add_filter( 'is_protected_meta', 'eprf_hide_custom_field_from_admin', 10, 3 );