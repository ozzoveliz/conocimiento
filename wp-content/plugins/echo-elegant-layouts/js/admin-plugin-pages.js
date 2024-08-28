jQuery(document).ready(function($) {

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
        $('#elay_license_check').html('');

        var postData = {
            action: 'elay_handle_license_request',
            command: 'get_license_info'
        };

        send_request( 'GET', postData, 'Retrieving license status. Please wait.' );
    }
    if ( $('#eckb_license_tab').hasClass('active') ) {
        check_license_status();
    }

    /* CHECK LICENSE STATUS when user opens the page */
    $('#wpbody').on('click', '#eckb_license_tab', function (e) {
        check_license_status();
    });

    /* SAVE ELAY LICENSE; runs for just ELAY license field */
    license_form.on('click', '#elay_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        var postData = {
            action: 'elay_handle_license_request',
            elay_license_key: $('#elay_license_key').val().trim(),
            _wpnonce_elay_license_key: $('#_wpnonce_elay_license_key').val(),
            command: 'save'
        };

        send_request( 'POST', postData, 'Saving license...' );
    });

    function send_request( ajax_type, postData, action_msg ) {

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
	            elay_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : elay_admin_notification('', 'Elegant Layouts: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        elay_admin_notification('', 'Please reload the page and try again (L37).', 'error');
            $('#elay_license_check').html(output);

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = elay_admin_notification('Elegant Layouts: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

	        elay_loading_Dialog( 'remove', '' );
            if ( msg ) {
				$('.eckb-top-notice-message').remove();
				$('body').append(msg);
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
	function elay_loading_Dialog( displayType, message ){

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
        $('#elay-ajax-in-progress').dialog({
            resizable: false,
            height: 70,
            width: 200,
            modal: false,
            autoOpen: false
        }).hide();
    }

    // SHOW INFO MESSAGES
    function elay_admin_notification( $title, $message , $type ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
            '</div>' +
            '</div>';
    }

	function elay_bottom_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'<div class="epkb-close-notice epkbfa epkbfa-window-close"></div>' +
			'</div>';
	}
	
	/** KB Configuration TABS scripts */
	
	// Other -> Sidebar Intro saving 
	$( '#elay-config-page__intro-settings' ).on( 'submit', function(){
		
		// Remove old messages
		$('.eckb-top-notice-message, .eckb-bottom-notice-message').remove();
		let kb_id = $( this ).find( '#elay-kb-id' ).val();

		let theResponse, msg, postData = {
			action: 'elay_save_sidebar_intro_text',
			_wpnonce_elay_ajax_action: elay_vars.nonce,
			sidebar_main_page_intro_text: $( this ).find( '[name="sidebar_main_page_intro_text"]').val(),
			elay_kb_id: kb_id,
			epkb_kb_id: kb_id,  // let KB core know about KB id in AJAX call
		};

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: postData,
			beforeSend: function (xhr)
			{
				elay_loading_Dialog( 'show' );
			}
		}).done(function (response)
		{
			theResponse = ( response ? response : '' );
			if ( theResponse.error || typeof theResponse.message === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				msg = theResponse.message ? theResponse.message : elay_bottom_admin_notification('', 'Please reload the page and try again (L37).', 'error');
				return;
			}
		}).fail( function ( response, textStatus, error )
		{
			//noinspection JSUnresolvedVariable
			msg = ( error ? ' [' + error + ']' : 'unknown error' );
			//noinspection JSUnresolvedVariable
			msg = elay_bottom_admin_notification( 'Elegant Layouts: Error occurred. Please try again later. (L02)', msg, 'error');
		}).always(function ()
		{

			elay_loading_Dialog( 'remove', '' );

			if ( ! theResponse.error && typeof theResponse.message !== 'undefined' ) {
				msg = elay_bottom_admin_notification( '', theResponse.message, 'success' );
			}

			if ( msg ) {
				$('body').append(msg).removeClass('fadeOutDown');;
			}
		});
		
		return false;
	});
});