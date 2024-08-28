jQuery(document).ready(function($) {

    var amcr_container = $( '#amag-access-manager-container' );
	var amcr_wp_roles_access_content =  $('#amcr-wp-roles-content');


	/** ***********************************************************************************************
	 *
	 *          PAGE CONTROLS
	 *
	 * **********************************************************************************************/

	/* Top Nav -----------------------------------------------------------------*/
	// Toggles the top Navigation ( KB, Config, Access ) to active status and displays their content.
	amcr_container.find( '#am'+'gr-config-main-nav' ).on( 'click', '.amag-nav-button', function(){

		//If Loading Icon is running don't allow for nav to change.
		if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
			return;
		}

		var navID = $( this ).attr( 'id' );

		// load appropriate content
		if ( navID === 'amag-wp-roles' ) {
			amcr_loading_saving_icon();
			amcr_load_wp_role_tab_content();
		}
	});


	/** ***********************************************************************************************
	 *
	 *          WP ROLE MAPPING CONTENT: View, Add, Delete
	 *
	 * **********************************************************************************************/

	// Load Access tab
	function amcr_switch_display_access_content_wp_roles( response ) {
		amcr_wp_roles_access_content.html();
		amcr_wp_roles_access_content.html( response.message );
		$('.amag-config-content').hide();
		$('#amcr-wp-roles-content').show();
	}

	/* WP ROLES button */
	amcr_wp_roles_access_content.on( 'click', '#amcr-add-wp-role-map', function(){

		//Set Button as active
		$( this ).toggleClass( 'amcr-btn-active' );

		//Show Input field
		$( '#amcr-add-wp-role-map-input' ).fadeToggle().css("display","inline-block");
	});

	// WP ROLES ADD action
	amcr_wp_roles_access_content.on( 'click', '#amcr_add_wp_role_map_ajax', function() {

		var wp_role_name = $( '#create-wp-role-name').val();
		var kb_group_id = $( '#create-kb-group-id').val();
		var kb_role_name = $( '#create-kb-role-name').val();

		amcr_loading_saving_icon();
		amcr_wp_role_operation( 'amcr_add_wp_role_ajax', wp_role_name, kb_role_name, kb_group_id, amcr_load_wp_role_tab_content );

		//Clear the value in the wp role name and deactivate the name inputs
		//Remove active status
		amcr_wp_roles_access_content.find( '#amcr-add-wp-role-map').removeClass('amcr-btn-active');
		//Clear Input value
		amcr_wp_roles_access_content.find( '#create-wp-role-name').val(' ');
		//Hide Name inputs
		$( '#amcr-add-wp-role-map-input' ).fadeOut();
	});

	/* WP ROLES DELETE button */
	amcr_wp_roles_access_content.on( 'click', '[id^=amcr-delete-wp-role-map]', function(){

		//Remove other confirmation boxes
		$( '.amag-popup' ).remove();

		// ensure a role was selected
		var wp_role_name = $(this).parent().parent().find('.amcr_wp_role_name').text();
		var kb_group_id = $(this).parent().parent().find('.amcr_kb_group_id').val();
		var kb_role_name = $(this).parent().parent().find('.amcr_kb_role_name').text();
		var msg = amcr_validate_wp_role_name( wp_role_name, kb_role_name );
		if ( msg ) {
			$('.eckb-bottom-notice-message').replaceWith(msg);
			$( "html, body" ).animate( {scrollTop: 0}, "slow" );
			return;
		}

		amcr_loading_saving_icon();
		amcr_wp_role_operation( 'amcr_delete_wp_role_ajax', wp_role_name, kb_role_name, kb_group_id, amcr_load_wp_role_tab_content );
	});

	// Call back-end for add/delete WP ROLES operations
	function amcr_wp_role_operation( operation, wp_role_name, kb_role_name, kb_group_id, callback ) {

		var msg = amcr_validate_wp_role_name( wp_role_name, kb_role_name );
		if ( msg ) {
			$('.eckb-bottom-notice-message').replaceWith(msg);
			$( "html, body" ).animate( {scrollTop: 0}, "slow" );
			return;
		}

		var postData = {
			action: operation,
			amag_kb_id: $('#amag_kb_id').val(),
			amcr_wp_role_name: wp_role_name,
			amcr_kb_group_id: kb_group_id,
			amcr_kb_role_name: kb_role_name,
			_wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax').val()
		};

		amcr_send_ajax( postData, callback );
	}

	function amcr_validate_wp_role_name( wp_role_name, kb_role_name ) {
		//var msg;

		if ( ! kb_role_name || kb_role_name.size < 3 ) {
			//msg = amcr_admin_notification('', 'Role Name has to be at least 3 characters long.', 'error');
			return false;
		}
		if ( ! wp_role_name || wp_role_name.size < 3 ) {
			//msg = amcr_admin_notification('', 'Role Name has to be at least 3 characters long.', 'error');
			return false;
		}
		return '';
	}

	/** display WP ROLES MAPPING content */
	function amcr_load_wp_role_tab_content() {

		var postData = {
			action: 'amcr_display_wp_roles_tabs_ajax',
			amag_kb_id: $('#amag_kb_id').val(),
			_wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax').val()
		};

		amcr_send_ajax( postData, amcr_switch_display_access_content_wp_roles );
	}

	
    /** ***********************************************************************************************
     *
     *          Other
     *
     * **********************************************************************************************/

    // SAVE Access Manager configuration
    $('#amcr-access-save-configuration-container').on( 'click', '#amcr_save_kb_config', function(e) {
        e.preventDefault();

        amcr_loading_saving_icon();

        var postData = {
            action: 'amcr_save_kb_config_changes',
            amag_kb_id: $('#amag_kb_id').val(),
            form: $('#amcr-access-configuration').serialize(),
            _wpnonce_amcr_save_kb_config: $('#_wpnonce_amcr_save_kb_config').val()
        };

        amcr_send_ajax( postData, amcr_callback_config_saved );
    });
    function amcr_callback_config_saved( response ) {
        $( '#amcr-user-tabs-section' ).html( response.message );
    }

	/** ***********************************************************************************************
	 *
	 *          AJAX calls
	 *
	 * **********************************************************************************************/


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
				amcr_container.find( '.amag-ajax-loading-saving-icon' ).remove();

				$('.eckb-bottom-notice-message').replaceWith(errorMsg);
			} else {
				//Complete Spinner animation.
				amcr_loading_saving_icon_complete();

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

	function amcr_callback_noop( response ){}

	// Load Ajax Loading / Saving Icon
	function amcr_loading_saving_icon(){

		//If Loading Icon already exists then don't bother loading another one.
		if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
			return;
		}
		amcr_container.find( '.amag-ajax-loading-saving-icon' ).remove();

		var html = '<div class="amag-ajax-loading-saving-icon">' +
			'<div class="amag-loading-spinner"></div>' +
			'</div>';//amag-ajax-loading-saving-icon
		amcr_container.append( html );

	}

	// Show Success Checkbox inside Icon then remove it after set time.
	function amcr_loading_saving_icon_complete(){

		//Only run the completed icon if the loading icon exists.
		if( $( '.amag-ajax-loading-saving-icon').length  !== 0 ){
			amcr_container.find( '.amag-loading-spinner').after( '<div class="epkbfa epkbfa-check-circle"></div>');
			amcr_container.find( '.amag-loading-spinner' ).remove();

			//postpone then remove:
			var amcr_loading_timeout;
			clearTimeout(amcr_loading_timeout);

			//Add fadeout class to notice after set amount of time has passed.
			amcr_loading_timeout = setTimeout(function () {
				amcr_container.find( '.amag-ajax-loading-saving-icon' ).remove();
			} , 1000);
		}
	}


	/** ***********************************************************************************************
	 *
	 *          Pop Ups and ckeckboxes
	 *
	 * **********************************************************************************************/

	// SHOW INFO MESSAGES
	function amcr_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'<div class="amcr-close-notice epkbfa epkbfa-window-close"></div>'+
			'</div>';
	}

    //Show or Hide checkboxes and submit button for Notification box on toggle button click.
    amcr_container.find( '.amag-notification-box' ).on( 'click', '.amag-content-toggle', function(){

        $( this ).parents( '.amag-collapse').find( '.amag-body-checkboxes').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.amcr-footer').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.fa').toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up');
        $( this ).parents( '.amag-collapse').toggleClass( 'amag-active-toggle' );
    });

    //Show or hide Toggle Content when title is clicked.
    amcr_container.on( 'click', '.amcr-toggle-box-header', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amcr-toggle-closed amcr-toggle-open ' );
        //Switch Icon pointer
        $( this ).find( '.amcr-toggle-box-icon' ).toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up ' );
    });

    //Show or hide Info Box Content when Icon is clicked.
    amcr_container.on( 'click', '.amcr-info-box-icon', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amcr-info-toggle-closed amcr-info-toggle-open ' );

    });
    //Close Info Box Content when X Icon is clicked.
    amcr_container.on( 'click', '.amcr-info-box-close', function(){

        //Hide Container
        $( this ).parents( '.amcr-info-box' ).toggleClass( 'amcr-info-toggle-closed amcr-info-toggle-open ' );

    });

    //Expand Info Box Content when Expand Icon is clicked.
    amcr_container.on( 'click', '.amcr-info-box-icon-max', function(){

        //Hide Container
        $( this ).parents( '.amcr-info-box' ).addClass( 'amcr-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amcr-info-box-icon-min' ).show();
    });
    //Compress Info Box Content when Compress Icon is clicked.
    amcr_container.on( 'click', '.amcr-info-box-icon-min', function(){

        //Hide Container
        $( this ).parents( '.amcr-info-box' ).removeClass( 'amcr-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amcr-info-box-icon-max' ).show();
    });
});