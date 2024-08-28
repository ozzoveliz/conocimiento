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
        $('#widg_license_check').html('');

        var postData = {
            action: 'widg_handle_license_request',
            command: 'get_license_info'
        };

        send_request( 'GET', postData, 'Retrieving license status. Please wait.', widg_admin_license_status_handler );
    }
    if ( $('#eckb_license_tab').hasClass('active') ) {
        check_license_status();
    }

    /* CHECK LICENSE STATUS when user opens the page. */
    $('#wpbody').on('click', '#eckb_license_tab', function (e) {
        check_license_status();
    });

    /* SAVE WIDG LICENSE; runs for just WIDG license field */
    license_form.on('click', '#widg_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        var postData = {
            action: 'widg_handle_license_request',
            widg_license_key: $('#widg_license_key').val().trim(),
            _wpnonce_widg_license_key: $('#_wpnonce_widg_license_key').val(),
            command: 'save'
        };

        send_request( 'POST', postData, 'Saving license...', widg_admin_license_status_handler );
    });

    function widg_admin_license_status_handler( output ) {
		  $('#widg_license_check').html(output);
	  }


	/*********************************************************************************************
	 *********************************************************************************************
	 *
	 *                OTHER
	 *
	 * ********************************************************************************************
	 ********************************************************************************************/

    function send_request( ajax_type, postData, action_msg, handler ) {

        var msg;

        $('.eckb-top-notice-message').html('');

        $.ajax({
            type: ajax_type,
            dataType: 'json',
            data: postData,
            url: widg_vars.ajaxurl,
            beforeSend: function (xhr)
            {
                //noinspection JSUnresolvedVariable
                widg_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : widg_admin_notification('', 'Widgets: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        widg_admin_notification('', 'Please reload the page and try again (L37).', 'error');
	        handler( output );

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = widg_admin_notification('Widgets: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

            widg_loading_Dialog( 'remove', '' );
            if ( msg ) {
                $('.eckb-top-notice-message').replaceWith(msg);
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
    function widg_loading_Dialog( displayType, message ){

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
        $('#widg-ajax-in-progress').dialog({
            resizable: false,
            height: 70,
            width: 200,
            modal: false,
            autoOpen: false
        }).hide();
    }

    // SHOW INFO MESSAGES
    function widg_admin_notification( $title, $message , $type ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
            '</div>' +
            '</div>';
    }

    //Show Widget Config Sidebar
    $( '#ep'+'kb-admin-mega-menu' ).on( 'click', '#eckb-mm-ap-links-widgets', function(){

        $('.widg-widgets-sidebar-config-content').addClass('ep'+'kb-mm-active');

    });
});
