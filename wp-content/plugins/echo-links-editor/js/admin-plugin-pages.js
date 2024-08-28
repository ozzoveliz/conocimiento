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
        $('#kblk_license_check').html('');

        var postData = {
            action: 'kblk_handle_license_request',
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

    /* SAVE LINK LICENSE; runs for just LINK license field */
    license_form.on('click', '#kblk_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        var postData = {
            action: 'kblk_handle_license_request',
            kblk_license_key: $('#kblk_license_key').val().trim(),
            _wpnonce_kblk_license_key: $('#_wpnonce_kblk_license_key').val(),
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
                kblk_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : kblk_admin_notification('', 'Links Editor: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        kblk_admin_notification('', 'Please reload the page and try again (L37).', 'error');
            $('#kblk_license_check').html(output);

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = kblk_admin_notification('Links Editor: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

            kblk_loading_Dialog( 'remove', '' );
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
    function kblk_loading_Dialog( displayType, message ){

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

    /* Dialogs --------------------------------------------------------------------------*/

    // SAVE AJAX-IN-PROGRESS DIALOG
    function setup_ajax_in_progress_dialog() {
        $('#kblk-ajax-in-progress').dialog({
            resizable: false,
            height: 70,
            width: 200,
            modal: false,
            autoOpen: false
        }).hide();
    }

    // SHOW INFO MESSAGES
    function kblk_admin_notification( $title, $message , $type ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
            '</div>' +
            '</div>';
    }

	/* ALL ARTICLE PAGE -----------------------------------------------------------------*/


	//Add New Button ( Add New LINK Article )
	( function () {
		//Check if KB All article page
		if($( 'body' ).is('[class*="post-type-ep' + 'kb_post_type"]')) {

			//If Add New Article button exists
			if( $( '.page-title-action' ).length > 0 ) {
				$( '.page-title-action' ).after( kblk_vars.add_link_tag );
			}
		}
	})();


	/* SINGLE ARTICLE PAGE --------------------------------------------------------------*/
    $( '.kblk-article-link-switch label' ).on( 'click' , function () {

        var checkbox = $( this ).parent().find( '#kblk-link-editor-on:checked' ).length;

        //Not Checked, Show Regular Article Content and Hide KBLK Content
        if( checkbox === 0 ){

            //Update KBLK Switch Icons and styling and text
            $( this ).parent().removeClass( 'kblk-checked' );
            $( '.kblk-switch-icon' ).addClass( 'epkbfa-link' );
            $( '.kblk-switch-icon' ).removeClass( 'epkbfa-id-card-o' );
            $( '.kblk-switch-text' ).text( 'Switch to Link' );
            //Hide KBLK Content
            $( '.kblk-article-link-container' ).hide();
            //Show Regular Content
            $( '.wp-editor-wrap' ).removeClass( 'kblk-hide' );
            $( '#post-status-info' ).removeClass( 'kblk-hide' );
            $( '#postbox-container-2' ).removeClass( 'kblk-hide' );
            $( '.mce-container' ).css( {'width':'100%'} );
            $( '.wp-editor-tools' ).css( {'width':'100%'} );

            $('#kblk_link_editor_mode').val('no');

            //Add Scroll to top to fix tinymce editor box collapse after displaying again.
            $("html, body").animate({ scrollTop: 0 }, "slow");
        }
        //Checked, Hide Regular Article Content and Show KBLK Content
        else {
            //Update KBLK Switch Icons and text
            $( this ).parent().addClass( 'kblk-checked' );
            $( '.kblk-switch-icon' ).addClass( 'epkbfa-id-card-o' );
            $( '.kblk-switch-icon' ).removeClass( 'epkbfa-link' );
            $( '.kblk-switch-text' ).text( 'Use Default Editor' );
            //Show KBLK Content
            $( '.kblk-article-link-container' ).fadeIn();
            //Hide Regular Content
            $( '.wp-editor-wrap' ).addClass( 'kblk-hide' );
            $( '#post-status-info' ).addClass( 'kblk-hide' );
            $( '#postbox-container-2' ).addClass( 'kblk-hide' );

            $('#kblk_link_editor_mode').val('yes');
        }

    });

    //If Switch is active Hide Default editor
    if( $( '.kblk-article-link-switch' ).hasClass( 'kblk-checked') ){
	    //Hide Regular Content
	    $( '.wp-editor-wrap' ).addClass( 'kblk-hide' );
	    $( '#post-status-info' ).addClass( 'kblk-hide' );
	    $( '#postbox-container-2' ).addClass( 'kblk-hide' );
    }


    //Show more Icons
    $( '.kblk-more-icon' ).on( 'click', function(){

        $( '.kblk-misc-icons ul' ).slideToggle();

    });

});
