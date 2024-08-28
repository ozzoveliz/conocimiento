<?php
/**
 * Functions used to duplicate event
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon/Admin
 * @version     lite 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Duplicate event action.
	function eventon_duplicate_event() {
		if ( empty( $_REQUEST['post']) ) {
			wp_die( esc_html(__( 'No event to duplicate has been supplied!', 'eventon' )) );
		}

		if( $_REQUEST['action'] != 'duplicate_event') return;

		$event_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';
				
		check_admin_referer( 'eventon-duplicate-event_' . $event_id );

		$post = eventon_get_event_to_duplicate( $event_id );

		// Copy the page and insert it
		if (isset($post) && $post!=null) {
			
			$new_id = eventon_create_duplicate_from_event($post);

			if($new_id){
				$EVENT = new EVO_Event( $new_id);
				// hook after duplicate event created
				do_action( 'eventon_duplicate_product', $new_id, $post );

				do_action('evo_after_duplicate_event', $EVENT, $post);

				// Redirect to the edit screen for the new draft page
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
				exit;
			}else{
				wp_die( esc_html(__( 'Event creation failed, could not create duplicate event:', 'eventon' )) . ' ' . esc_attr( $id) );
			}
			
		} else {
			wp_die( esc_html(__( 'Event creation failed, could not find original event:', 'eventon' )) . ' ' . esc_attr( $id ) );
		}
	}


// Duplication of the post
	function eventon_create_duplicate_from_event( $post, $parent = 0, $post_status = '' ) {
		global $wpdb;

		$new_post_author 	= wp_get_current_user();
		$new_post_date 		= current_time('mysql');
		$new_post_date_gmt 	= get_gmt_from_date($new_post_date);

		if ( $parent > 0 ) {
			$post_parent		= $parent;
			$post_status 		= $post_status ? $post_status : 'publish';
			$suffix 			= '';
		} else {
			$post_parent		= $post->post_parent;
			$post_status 		= $post_status ? $post_status : 'draft';
			$suffix 			= apply_filters('evo_duplicate_eventname_suffix', ' ' . __( '(Copy)', 'eventon' ) );
		}

		// Insert the new template in the post table
		$new_post = array(
		    'post_author'           => $new_post_author->ID,
		    'post_date'             => $new_post_date,
		    'post_date_gmt'         => $new_post_date_gmt,
		    'post_content'          => $post->post_content,
		    'post_content_filtered' => $post->post_content_filtered,
		    'post_title'            => $post->post_title . $suffix,
		    'post_excerpt'          => $post->post_excerpt,
		    'post_status'           => $post_status,
		    'post_type'             => $post->post_type,
		    'comment_status'        => $post->comment_status,
		    'ping_status'           => $post->ping_status,
		    'post_password'         => $post->post_password,
		    'to_ping'               => $post->to_ping,
		    'pinged'                => $post->pinged,
		    'post_modified'         => $new_post_date,
		    'post_modified_gmt'     => $new_post_date_gmt,
		    'post_parent'           => $post_parent,
		    'menu_order'            => $post->menu_order,
		    'post_mime_type'        => $post->post_mime_type,
		);

		// Insert the post into the database
		$new_post_id = wp_insert_post($new_post);

		// Check for errors
		if (is_wp_error($new_post_id)) {
		    // Handle the error
		    $error_message = $new_post_id->get_error_message();
		    
		    return false;
		} else {
		    // The post was inserted successfully
		   
		   // Copy the taxonomies
			eventon_duplicate_post_taxonomies( $post->ID, $new_post_id, $post->post_type );

			// Copy the meta information
			eventon_duplicate_post_meta( $post->ID, $new_post_id );

			return $new_post_id;
		}	

	}

/** Get a event from the database to duplicate */
	function eventon_get_event_to_duplicate($id) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) return false;

		// Get the post object using WordPress function
    	$post = get_post($id);

    	// If the post is a revision, get its parent post
	    if ($post->post_type == "revision") {
	        $parent_id = wp_is_post_revision($id);
	        if ($parent_id) {
	            $post = get_post($parent_id);
	        }
	    }

	    return $post;

	}

// duplicate event taxonomies
	function eventon_duplicate_post_taxonomies($id, $new_id, $post_type) {
		global $wpdb;
		$taxonomies = get_object_taxonomies($post_type); //array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($id, $taxonomy);
			$post_terms_count = sizeof( $post_terms );
			for ($i=0; $i<$post_terms_count; $i++) {
				wp_set_object_terms($new_id, $post_terms[$i]->slug, $taxonomy, true);
			}
		}
	}

// Duplicate post meta for the event
	function eventon_duplicate_post_meta($id, $new_id) {
		

		$all_meta = get_post_meta( $id );

		if (!empty($all_meta)) {

			$meta_exclude = apply_filters( 'eventon_duplicate_event_exclude_meta', array('tx_woocommerce_product_id') );

			foreach($meta_exclude as $key){
				if( isset( $all_meta[ $key ])) unset( $all_meta[ $key ] );
			}

			// insert post meta into new event post
			foreach ($all_meta as $meta_key => $meta_values) {
		        foreach ($meta_values as $meta_value) {
		            update_post_meta($new_id, $meta_key, $meta_value);
		        }
		    }
		}


		
	}
?>