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
        $('#amcr_license_check').html('');

        var postData = {
            action: 'amcr_handle_license_request',
            command: 'get_license_info'
        };

	    send_ajax_request( 'GET', postData, 'Retrieving license status. Please wait.' );
    }
    if ( $('#eckb_license_tab').hasClass('active') ) {
        check_license_status();
    }

    /* CHECK LICENSE STATUS when user opens the page */
    $('#wpbody').on('click', '#eckb_license_tab', function (e) {
        check_license_status();
    });

    /* SAVE LINK LICENSE; runs for just LINK license field */
    license_form.on('click', '#amcr_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        var postData = {
            action: 'amcr_handle_license_request',
            amcr_license_key: $('#amcr_license_key').val().trim(),
            _wpnonce_amcr_license_key: $('#_wpnonce_amcr_license_key').val(),
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
				amcr_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : amcr_admin_notification('', 'Custom Roles: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        amcr_admin_notification('', 'Please reload the page and try again (L37).', 'error');
            $('#amcr_license_check').html(output);

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = amcr_admin_notification('Custom Roles: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

            amcr_loading_Dialog( 'remove', '' );
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
    function amcr_loading_Dialog( displayType, message ){ 

        if( displayType === 'show' ){

            var output =
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
		$('#amcr-ajax-in-progress').dialog({
			resizable: false,
			height: 70,
			width: 200,
			modal: false,
			autoOpen: false
		}).hide();
	}

	// SHOW INFO MESSAGES
	function amcr_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'<div class="amcr-close-notice fa fa-window-close"></div>'+
			'</div>';
	}

    /* Clear Logs For Bottom Notice */
    $( 'body' ).on( 'click', '.amcr_notice_reset_logs_ajax', function(){
        var postData = {
            action: 'amcr_reset_logs_ajax',
            _wpnonce_epkb_ajax_action: amgr_vars.nonce
        };

        amcr_send_ajax( postData, null, true );
    });


    // generic AJAX call handler
    function amcr_send_ajax( postData, refreshCallback, reload ) {

        var errorMsg;
        var theResponse;
        var refreshCallback = (typeof refreshCallback === 'undefined') ? 'amcr_callback_noop' : refreshCallback;

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: postData,
            url: ajaxurl,
            beforeSend: function (xhr)
            {
                $('#amcr-ajax-in-progress').text('Working ...').dialog('open');
            }
        }).done(function (response)        {
            theResponse = ( response ? response : '' );
            if ( theResponse.error || typeof theResponse.message === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                errorMsg = theResponse.message ? theResponse.message : amcr_admin_notification('', amcr_vars.reload_try_again, 'error');
            }

        }).fail( function ( response, textStatus, error )        {
            //noinspection JSUnresolvedVariable
            errorMsg = ( error ? ' [' + error + ']' : amcr_vars.unknown_error );
            //noinspection JSUnresolvedVariable
            errorMsg = amcr_admin_notification(amcr_vars.error_occurred + '. ' + amcr_vars.msg_try_again, errorMsg, 'error');
        }).always(function ()        {
            $('#amcr-ajax-in-progress').dialog('close');

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

});
