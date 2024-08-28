<?php

/*** GENERIC NON-KB functions  ***/

/**
 * Hide post meta from Admin
 * @param $protected
 * @param $meta_key
 * @param $meta_type
 * @return bool
 */
function kblk_hide_custom_field_from_admin( $protected, $meta_key, $meta_type ) {
	$protected_meta = array( 'kblk-link-editor-data' );
	return in_array( $meta_key, $protected_meta ) ? true : $protected;
}
add_filter( 'is_protected_meta', 'kblk_hide_custom_field_from_admin', 10, 3 );