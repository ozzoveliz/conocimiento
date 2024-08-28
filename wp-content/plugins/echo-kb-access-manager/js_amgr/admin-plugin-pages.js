jQuery(document).ready(function($) {

	// Remove default 'Categories' list for WP Block Editor
	if ( wp && wp.data && wp.data.dispatch( 'core/edit-post' ) ) {
		wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'taxonomy-panel-' + amgr_vars.kb_taxonomy_name );
	}

	setup_ajax_in_progress_dialog();


	/*********************************************************************************************
	 *********************************************************************************************
	 *
	 *                MANAGE LICENSE
	 *
	 * ********************************************************************************************
	 ********************************************************************************************/

	var license_form = $('#ekcb-licenses');

	function check_license_status() {
		$('#amgr_license_check').html('');

		var postData = {
			action: 'amgr_handle_license_request',
			command: 'get_license_info'
		};

		send_ajax_request( 'GET', postData, 'Retrieving license status. Please wait.' );
	}

	/* CHECK LICENSE STATUS when user opens the page */
	$('#wpbody').on('click', '#eckb_license_tab', function (e) {
		check_license_status();
	});

	/* SAVE AMGR LICENSE; runs for just AMGR license field */
	license_form.on('click', '#amgr_save_btn', function (e) {
		e.preventDefault();  // do not submit the form

		var postData = {
			action: 'amgr_handle_license_request',
			amgr_license_key: $('#amgr_license_key').val().trim(),
			_wpnonce_amgr_license_key: $('#_wpnonce_amgr_license_key').val(),
			command: 'save'
		};

		send_ajax_request( 'POST', postData, 'Saving license...' );
	});

	function send_ajax_request( ajax_type, postData, action_msg ) {

		var msg;

		$('.eckb-top-notice-message').html('');

		$.ajax({
			type: ajax_type,
			dataType: 'json',
			data: postData,
			url: ajaxurl,
			beforeSend: function (xhr)
			{
				//noinspection JSUnresolvedVariable
				//$('#amgr-ajax-in-progress').text(action_msg).dialog('open');
				amgr_loading_Dialog( 'show', action_msg );

			}
		}).done(function (response) {

			response = ( response ? response : '' );
			if ( response.message || typeof response.output === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				msg = response.message ? response.message : amgr_admin_notification('', 'Access Manager: Error occurred. Please try again later. (L01)', 'error');
				return;
			}

			var output = typeof response.output !== 'undefined' && response.output ? response.output :
				amgr_admin_notification('', 'Please reload the page and try again (L37).', 'error');
			$('#amgr_license_check').html(output);

		}).fail(function (response, textStatus, error) {
			msg = ( error ? ' [' + error + ']' : 'unknown error' );
			msg = amgr_admin_notification('Access Manager: Error occurred. Please try again later. (L02)', msg, 'error');
		}).always(function () {

			//$('#amgr-ajax-in-progress').dialog('close');
			amgr_loading_Dialog( 'remove', '');
			if ( msg ) {
				$('.eckb-bottom-notice-message').replaceWith(msg);
				$( "html, body" ).animate( {scrollTop: 0}, "slow" );
			}
		});

	}

	/**
	 * Displays a Center Dialog box with a loading icon and text.
	 *
	 * This should only be used for indicating users that loading or saving or processing is in progress, nothing else.
	 * This code is used in these files, any changes here must be done to the following files.
	 *   - admin-plugin-pages.js
	 *   - admin-kb-config-scripts.js
	 *   - admin-kb-wizard-script.js
	 *
	 * @param  {string}    displayType     Show or hide Dialog initially. ( show, remove )
	 * @param  {string}    message         Optional    Message output from database or settings.
	 *
	 * @return {html}                      Removes old dialogs and adds the HTML to the end body tag with optional message.
	 *
	 */
	function amgr_loading_Dialog( displayType, message ){

		if( displayType === 'show' ){

			let output =
				'<div class="epkb-admin-dialog-box-loading">' +

				//<-- Header -->
				'<div class="epkb-admin-dbl__header">' +
				'<div class="epkb-admin-dbl-icon epkbfa epkbfa-hourglass-half"></div>'+
				(message ? '<div class="epkb-admin-text">' + message + '</div>' : '' ) +
				'</div>'+

				'</div>' +
				'<div class="epkb-admin-dialog-box-overlay"></div>';

			//Add message output at the end of Body Tag
			$( 'body' ).append( output );

		}else if( displayType === 'remove' ){

			// Remove loading dialogs.
			$( '.epkb-admin-dialog-box-loading' ).remove();
			$( '.epkb-admin-dialog-box-overlay' ).remove();
		}

	}


	/* Dialogs --------------------------------------------------------------------*/

	// SAVE AJAX-IN-PROGRESS DIALOG
	function setup_ajax_in_progress_dialog() {
		$('#amgr-ajax-in-progress').dialog({
			resizable: false,
			height: 70,
			width: 200,
			modal: false,
			autoOpen: false
		}).hide();
	}

	// SHOW INFO MESSAGES
	function amgr_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'<div class="epkb-close-notice fa fa-window-close"></div>'+
			'</div>';
	}



	/* ARTICLE PAGE ---------------------------------------------------------------*/

	/**
	 * Delay the sidebar display so that scripts can do their thing.
	 *  Note: Must use opacity since WP does weird behaviour if using display none / block.
	 */
	$( '.amag-private-admin .postbox-container').css( 'opacity', '0' );
	setTimeout( function () {

		$( '.amag-private-admin .postbox-container').css( 'opacity', '100' );

	}, 2000 );

	//Info Icon click
	var epkb = $( '#ekb-admin-page-wrap' );
	epkb.find( ' .amgr-access-config-inner' ).on('click', '.info-icon',  function () {
		$( this ).find( 'p').toggle();
	});


	/* Clear Logs For Bottom Notice */
	$( 'body' ).on( 'click', '.amgr_notice_reset_logs_ajax', function(){
		var postData = {
			action: 'amgr_reset_logs_ajax',
			_wpnonce_epkb_ajax_action: amgr_vars.nonce
		};

		amgr_send_ajax( postData, null, true );
	});

	// generic AJAX call handler
	function amgr_send_ajax( postData, refreshCallback, reload ) {

		var errorMsg;
		var theResponse;
		var refreshCallback = (typeof refreshCallback === 'undefined') ? 'amgr_callback_noop' : refreshCallback;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: ajaxurl,
			beforeSend: function (xhr)
			{
				$('#amgr-ajax-in-progress').text('Working ...').dialog('open');
			}
		}).done(function (response)        {
			theResponse = ( response ? response : '' );
			if ( theResponse.error || typeof theResponse.message === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				errorMsg = theResponse.message ? theResponse.message : amgr_admin_notification('', amgr_vars.reload_try_again, 'error');
			}

		}).fail( function ( response, textStatus, error )        {
			//noinspection JSUnresolvedVariable
			errorMsg = ( error ? ' [' + error + ']' : amgr_vars.unknown_error );
			//noinspection JSUnresolvedVariable
			errorMsg = amgr_admin_notification(amgr_vars.error_occurred + '. ' + amgr_vars.msg_try_again, errorMsg, 'error');
		}).always(function ()        {
			$('#amgr-ajax-in-progress').dialog('close');

			if ( errorMsg ) {
				$('.eckb-bottom-notice-message').replaceWith(errorMsg);
			} else {


				if ( typeof refreshCallback === "function" ) {
					theResponse = (typeof theResponse === 'undefined') ? '' : theResponse;
					refreshCallback(theResponse);
				} else {
					if ( reload ) {
						location.reload();
					}
				}
			}
		});
	}

	/* TOOLS PAGE ---------------------------------------------------------------*/

	/**
	 * Search User
	 */

	let search_user_timer = false;

	$('#amgr_debug_user_search').on('keyup', function () {
		// set task to search in 1.5s
		clearTimeout(search_user_timer);
		$('#amgr_debug_user_id').text( '' );
		$('#amgr_debug_user_name').text( '-' );
		$('#amgr_debug_user_email').text( '-' );
		$('#amgr_enable_debug_user_access').prop('disabled', true);

		if ( ! $(this).val() ) {
			return;
		}

		search_user_timer = setTimeout(function () {

			let postData = {
				action: 'amgr_search_user',
				search: $( '#amgr_debug_user_search' ).val(),
				_wpnonce_epkb_ajax_action: epkb_vars.nonce
			};

			let errorMsg;

			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: postData,
				url: ajaxurl,
				beforeSend: function (xhr) {
					$('body').append( epkb_admin_notification('', amgr_vars.search_user, 'success') ).removeClass('fadeOutDown');
					$('#amgr_debug_user_search').blur();
					$('#amgr_debug_user_search').prop('disabled', true);
				}
			}).done(function (response) {

				if ( response.error || typeof response.message === 'undefined' ) {
					//noinspection JSUnresolvedVariable,JSUnusedAssignment
					errorMsg = response.message ? response.message : epkb_admin_notification('', amgr_vars.reload_try_again, 'error');
				}

				if ( typeof response.message != 'undefined' ) {
					errorMsg = response.message;
				}

				if ( typeof response.id !== 'undefined' ) {
					$('#amgr_debug_user_id').text( response.id );
				} else {
					$('#amgr_debug_user_id').text( amgr_vars.user_not_found );
				}

				if ( typeof response.name !== 'undefined' ) {
					$('#amgr_debug_user_name').text( response.name );
				}

				if ( typeof response.email !== 'undefined' ) {
					$('#amgr_debug_user_email').text( response.email );
				}

				if ( typeof response.message !== 'undefined' ) {
					$('#amgr_debug_user_email').val( response.email );
				}

			}).fail( function ( response, textStatus, error )        {
				//noinspection JSUnresolvedVariable
				errorMsg = ( error ? ' [' + error + ']' : epkb_vars.unknown_error );
				//noinspection JSUnresolvedVariable
				errorMsg = epkb_admin_notification(epkb_vars.error_occurred + '. ' + epkb_vars.msg_try_again, errorMsg, 'error');
			}).always(function() {
				$('.eckb-bottom-notice-message').remove();
				$('#amgr_debug_user_search').prop('disabled', false);
				$('#amgr_enable_debug_user_access').prop('disabled', false);

				if ( errorMsg ) {
					$('.eckb-bottom-notice-message').remove();
					$('body').append(errorMsg).removeClass('fadeOutDown');

					setTimeout( function() {
						$('.eckb-bottom-notice-message').addClass( 'fadeOutDown' );
					}, 10000 );
					return;
				}
			});
		}, 1500 );
	});

	$('#amgr_enable_debug_user_access').on('click', function () {

		let postData = {
			action: 'amgr_save_debug_user_data',
			id: $( '#amgr_debug_user_id' ).text(),
			enable: $('[name="amgr_debug_user_access"]').prop('checked') ? '1' : '',
			_wpnonce_epkb_ajax_action: epkb_vars.nonce
		};

		let errorMsg;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: ajaxurl,
			beforeSend: function (xhr) {
				epkb_loading_Dialog( 'show' );
			}
		}).done( function (response) {
			if ( response.error || typeof response.message === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				errorMsg = response.message ? response.message : epkb_admin_notification('', amgr_vars.reload_try_again, 'error');
			}

			if ( typeof response.message != 'undefined' ) {
				errorMsg = response.message;
			}

		}).fail( function ( response, textStatus, error )        {
			//noinspection JSUnresolvedVariable
			errorMsg = ( error ? ' [' + error + ']' : epkb_vars.unknown_error );
			//noinspection JSUnresolvedVariable
			errorMsg = epkb_admin_notification(epkb_vars.error_occurred + '. ' + epkb_vars.msg_try_again, errorMsg, 'error');
			}).always(function() {
			epkb_loading_Dialog('remove', '');

			if ( errorMsg ) {
				$('.eckb-bottom-notice-message').remove();
				$('body').append(errorMsg).removeClass('fadeOutDown');

				setTimeout( function() {
					$('.eckb-bottom-notice-message').addClass( 'fadeOutDown' );
				}, 10000 );
				return;
			}
		});

		return false;
	});

	/** AJAX help functions for tools page */
	function epkb_loading_Dialog( displayType, message ){

		if( displayType === 'show' ){

			let output =
				'<div class="epkb-admin-dialog-box-loading">' +

				//<-- Header -->
				'<div class="epkb-admin-dbl__header">' +
				'<div class="epkb-admin-dbl-icon epkbfa epkbfa-hourglass-half"></div>'+
				(message ? '<div class="epkb-admin-text">' + message + '</div>' : '' ) +
				'</div>'+

				'</div>' +
				'<div class="epkb-admin-dialog-box-overlay"></div>';

			//Add message output at the end of Body Tag
			$( 'body' ).append( output );
		} else if( displayType === 'remove' ){

			// Remove loading dialogs.
			$( '.epkb-admin-dialog-box-loading' ).remove();
			$( '.epkb-admin-dialog-box-overlay' ).remove();
		}

	}

	function epkb_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>' + $title + '</h4>' : '' ) +
			($message ? '<p>' + $message + '</p>': '') +
			'</span>' +
			'</div>' +
			'<div class="epkb-close-notice epkbfa epkbfa-window-close"></div>' +
			'</div>';
	}
});
