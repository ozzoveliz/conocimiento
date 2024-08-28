jQuery(document).ready(function($) {

    var amgp_container = $( '#amag-access-manager-container' );
    var amgp_groups_content = $('#amgp-kb-groups-content');
    var amgp_users_content =  $('#amgp-users-content');

	/** ***********************************************************************************************
	 *
	 *          PAGE CONTROLS
	 *
	 * **********************************************************************************************/

	/* Top Nav -----------------------------------------------------------------*/
	// Toggles the top Navigation ( KB, Config, Access ) to active status and displays their content.
	amgp_container.find( '#am'+'gr-config-main-nav' ).on( 'click', '.amag-nav-button', function(){

		//If Loading Icon is running don't allow for nav to change.
		if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
			return;
		}

		var navID = $( this ).attr( 'id' );

		// load appropriate content
	    if ( navID === 'amag-users' ) {
		    amgp_loading_saving_icon();
		    amgp_load_user_tab_content();
	    } else if ( navID === 'amag-groups' ) {
            amgp_loading_saving_icon();
            amgp_load_group_tab_content();
      } 
	});

    /** ***********************************************************************************************
     *
     *          GROUPS CONTENT: View, Add, Rename, Delete
     *
     * **********************************************************************************************/

    // Load Access tab
    function amgp_switch_display_access_content_kb_groups( response ) {
	    amgp_groups_content.html();
	    amgp_groups_content.html( response.message );
	    $('.amag-config-content').hide();
	    $('#amgp-kb-groups-content').show();
    }

    /* GROUP ADD button */
    amgp_groups_content.on( 'click', '#amgp-add-group', function(){

        //Set Button as active
        $( this ).toggleClass( 'amgp-btn-active' );

        //Show Input field
        $( '#amgp-add-group-input' ).fadeToggle().css("display","inline-block");
    });

    // GROUP ADD action
    amgp_groups_content.on( 'click', '#amgp_add_kb_group_ajax', function() {

        let kb_group_name = $( '#create-group-name').val().trim();

        amgp_loading_saving_icon();
        amgp_kb_group_operation( 'amgp_add_kb_group_ajax', 0, kb_group_name, amgp_load_group_tab_content );

        //Clear the value in the group name and deactivate the Group name inputs
        //Remove active status
        amgp_groups_content.find( '#amgp-add-group').removeClass('amgp-btn-active');
        //Clear Input value
        amgp_groups_content.find( '#create-group-name').val(' ');
        //Hide Group Name inputs
        $( '#amgp-add-group-input' ).fadeOut();
    });

    /* GROUP RENAME button */
    amgp_groups_content.on( 'click', '.amag-rename-group-toggle', function(e){
        e.preventDefault();

        //Show Input field
        $( this ).parent().find( '.amgp-rename-group-input' ).fadeToggle().css("display","inline-block");
    });

    // GROUP RENAME action
    amgp_groups_content.on( 'click', '[id^=amgp_rename_kb_group_ajax]', function(e) {
        e.preventDefault();

        let kb_group_id =  $(this).parent().parent().parent().find('.amgp_kb_group_id').val();
        let kb_group_name = $(this).parent().parent().find('#rename-group-name' + kb_group_id).val().trim();

        amgp_loading_saving_icon();
        amgp_kb_group_operation( 'amgp_rename_kb_group_ajax', kb_group_id, kb_group_name, amgp_load_group_tab_content );
    });

    /* GROUP DELETE button */
    amgp_groups_content.on( 'click', '[id^=amgp-delete-kb-group]', function(){

        //Remove other confirmation boxes
        $( '.amgp-popup' ).remove();

        // ensure a group was selected
        var kb_group_id = $(this).parent().find('.amgp_kb_group_id').val();
        var kb_group_name = $(this).parent().find('.amgp_kb_group_name').val();
        var msg = amgp_validate_group_id_name( kb_group_id, kb_group_name );
        if ( msg ) {
            $('.eckb-bottom-notice-message').replaceWith(msg);
            $( "html, body" ).animate( {scrollTop: 0}, "slow" );
            return;
        }

        amg_confirmation_box_with_checkboxes( {
            id:             "amag-delete-group-box",
            type:           "amag-error",
            kb_group_name:  kb_group_name,
            kb_group_id:    kb_group_id,
            apply_function: amgp_callback_confirmation_box,
            header:         "Deleting Group " + kb_group_name,
            content:        "<p>Deleting this group will remove all access to all users assigned to this group.</p>",
            checkboxes:     [
                "I understand that all settings assigned to this group will be lost.",
                "I acknowledge that this change is not reversible."],
            apply_button: "Delete Group",
            cancel_button:   "Cancel"
        } );
    });

    function amgp_callback_confirmation_box(kb_group_name, kb_group_id) {
        amgp_container.find( '#amag-delete-group-box' ).on( 'click', '#amag-notification-box-apply-js', function() {

            var error = 0;
            $( '.amag-notification-box' ).find( ' .amag-body-checkboxes li ' ).each( function (){

                //Go through each checkbox and check if checked. If not set error value
                if( !$( this ).find( 'input[type="checkbox"]:checked').length > 0 ){
                    error = 1;
                    $('.eckb-bottom-notice-message').replaceWith( amgp_admin_notification('', 'Must check all checkboxes before Deleting group', 'error') );
                }
            });

            //If all boxes checked submit the form.
            if ( error === 0 ) {
                amgp_loading_saving_icon();
                amgp_kb_group_operation('amgp_delete_kb_group_ajax', kb_group_id, kb_group_name, amgp_load_group_tab_content);
                $( '#amag-delete-group-box' ).hide();
            }
        });
    }

    // Call back-end for add/rename/delete Group operations
    function amgp_kb_group_operation( operation, kb_group_id, kb_group_name, callback ) {

        var msg = amgp_validate_group_id_name( kb_group_id, kb_group_name );
        if ( msg ) {
            $('.eckb-bottom-notice-message').replaceWith(msg);
            $( "html, body" ).animate( {scrollTop: 0}, "slow" );
            return;
        }

        var postData = {
            action: operation,
            amag_kb_id: $('#amag_kb_id').val(),
            amgp_kb_group_name: kb_group_name,
            amgp_kb_group_id: kb_group_id,
	        _wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax').val()
        };

        amgp_send_ajax( postData, callback );
    }

    function amgp_validate_group_id_name( kb_group_id, kb_group_name ) {
        //var msg;

        if ( typeof kb_group_name == 'undefined' || ! kb_group_name || kb_group_name.size < 3 ) {
            //msg = amgp_admin_notification('', 'Group Name has to be at least 3 characters long.', 'error');
            return false;
        }
        if ( typeof kb_group_id == 'undefined' || ! kb_group_id || kb_group_id === "0" ) {
            //msg = amgp_admin_notification('', 'Select Group first.', 'error');
            return false;
        }
        return '';
    }

    /** display KB Group content */
    function amgp_load_group_tab_content() {

        var postData = {
            action: 'amgp_add_kb_groups_tabs_ajax',
            amag_kb_id: $('#amag_kb_id').val(),
	        _wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax').val()
        };

        $('#amgp-delete-group-box').hide();
        amgp_send_ajax( postData, amgp_switch_display_access_content_kb_groups );
    }


    /** ***********************************************************************************************
     *
     *          USERS CONTENT: View, Add, Rename, Delete
     *
     * **********************************************************************************************/

    // Load Access tab
    function amgp_switch_display_access_content_users( response ) {
	    amgp_users_content.html();
	    amgp_users_content.html( response.message );
	    $('.amag-config-content').hide();
	    amgp_users_content.show();
    }

    // 1. Load Users based on selected KB Group
    amgp_users_content.on( 'change', '#amgp-user-tabs-kb-group-list', function() {
        var kb_group_id = $('#amgp-user-tabs-kb-group-list').find(":selected").val();
        if ( kb_group_id === '0' ) {
            $( '.amgp-user-tabs-container' ).remove();
            return;
        }

        amgp_loading_saving_icon();
        amgp_load_user_tab_content();
    });

    function amgp_load_user_tab_content() {
        var kb_group_id = amgp_users_content.find('#amgp-user-tabs-kb-group-list').find(":selected").val();
        // ignore user selecting "choose group" option
        var active_role = $( '#amgp-users-content .amgp-nav-tabs .amag-active-tab' ).attr( 'id' );

        if ( kb_group_id === "0" ) {
            $( '.amgp-user-tabs-container' ).remove();
        }

        var postData = {
            action: 'amgp_add_kb_user_roles_tabs_ajax',
            amag_kb_id: $('#amag_kb_id').val(),
            amgp_kb_group_id: kb_group_id,
			amag_current_page_number: $('#ammgp-page-number').data('page-number'),
            amgp_active_role: active_role,
	        _wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax' ).val()
        };

        amgp_send_ajax( postData, amgp_switch_display_access_content_users );
    }

    // ADD USER for given KB and KB Group
    amgp_users_content.on( 'click', '.amgp_add_kb_group_user_ajax', function() {
        var kb_group_id = $('#amgp-user-tabs-kb-group-list').find(":selected").val();
        var kb_role_name = $(this).closest('.amgp-group-roles').find('.amgp_kb_role_name').val();
        var wp_user_id = $(this).closest(".amgp-user-record").find(".amgp-wp-user-id").val();

        //Only show loading icon if there are users to add.
        if( wp_user_id > 0 ){
            amgp_loading_saving_icon();
        }

        amgp_kb_group_user_operation( 'amgp_add_kb_group_user_ajax', kb_group_id, kb_role_name, wp_user_id );
    });

    // REMOVE USER for given KB and KB Group
    amgp_users_content.on( 'click', '.amgp_remove_kb_group_user_ajax', function() {
    	$( this ).attr( 'disabled', 'disabled' );  // prevent error on double click
        var wp_user_id          = $( this ).parent().parent().find( '.amgp-wp-user-id' ).val();
        var kb_role_name        = $( this ).closest( '.amgp-group-roles' ).find( '.amgp_kb_role_name' ).val();
        var kb_group_id         = $( '#amgp-user-tabs-kb-group-list' ).find( ":selected" ).val();
        amgp_loading_saving_icon();
        amgp_kb_group_user_operation( 'amgp_remove_kb_group_user_ajax', kb_group_id, kb_role_name, wp_user_id );
    });

    // Call back-end for add/rename/delete Group User operations
    function amgp_kb_group_user_operation( operation, kb_group_id, kb_role_name, wp_user_id ) {
        var msg;

       msg = amgp_validate_group_user_operation( kb_group_id, kb_role_name, wp_user_id );
       if ( msg ) {
            $('.eckb-bottom-notice-message').replaceWith(msg);
            $( "html, body" ).animate( {scrollTop: 0}, "slow" );
            return;
        }

        var postData = {
            action: operation,
            amag_kb_id: $('#amag_kb_id').val(),
            amgp_kb_role_name: kb_role_name,
            amgp_wp_user_id: wp_user_id,
            amgp_kb_group_id: kb_group_id,
	        _wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax').val()
        };

         amgp_send_ajax( postData, amgp_load_user_tab_content );
    }

    // validate operation
    function amgp_validate_group_user_operation( kb_group_id, kb_role_name, wp_user_id ) {
        var msg;

        if ( ! kb_role_name || kb_role_name.size < 3 ) {
            msg = amgp_admin_notification('', 'Role name is invalid.', 'error');
            return msg;
        }
        if ( ! kb_group_id || kb_group_id === "0" ) {
            msg = amgp_admin_notification('', 'Select Group first.', 'error');
            return msg;
        }
        if ( wp_user_id === "0" ) {
            msg = amgp_admin_notification('', 'Select User first.', 'error');
            return msg;
        }
        return '';
    }

    // pagination of users
	amgp_users_content.on( 'click', "a", function(e) {  // a[href*='amag-user-page-number']
		e.preventDefault();
		var postData = {
			action: 'amgp_get_user_page_ajax',
			amag_kb_id: $('#amag_kb_id').val(),
			amgp_kb_group_id: $('#amgp-user-tabs-kb-group-list').find(":selected").val(),
			amgp_kb_role_name: $(this).closest('.amgp-group-roles').find('.amgp_kb_role_name').val(),
			amag_current_page_number: $('#ammgp-page-number').data('page-number'),
			amag_next_page_number: $(this).text(),
			_wpnonce_amgp_get_user_page_ajax: $('#_wpnonce_amgp_get_user_page_ajax').val()
		};

		amgp_loading_saving_icon();

		amgp_send_ajax( postData, amgp_callback_new_user_page );
	});
	function amgp_callback_new_user_page( response ) {
		$( '#amgp-control-' + response.role ).html();
		$( '#amgp-control-' + response.role ).html( response.message );
	}

    // search users
	amgp_users_content.on('click', "#amgp_filter_users", function(e){
		e.preventDefault();
		var amgp_kb_role_name = $(this).closest('.amgp-group-roles').find('.amgp_kb_role_name').val();
		var postData = {
			action: 'amgp_filter_users_ajax',
			amag_kb_id: $('#amag_kb_id').val(),
			amgp_kb_group_id: $('#amgp-user-tabs-kb-group-list').find(":selected").val(),
			amgp_kb_role_name: amgp_kb_role_name,
			amag_user_filter: $('#amgp_user_filter-' + amgp_kb_role_name).val(),
			_wpnonce_amgp_get_user_page_ajax: $('#_wpnonce_amgp_get_user_page_ajax').val()
		};

		amgp_loading_saving_icon();

		amgp_send_ajax( postData, amgp_callback_new_user_page );
	});

    /** ***********************************************************************************************
     *
     *          Other
     *
     * **********************************************************************************************/

    // SAVE Access Manager configuration
    $('#amgp-access-save-configuration-container').on( 'click', '#amgp_save_kb_config', function(e) {
        e.preventDefault();

        amgp_loading_saving_icon();

        var postData = {
            action: 'amgp_save_kb_config_changes',
            amag_kb_id: $('#amag_kb_id').val(),
            form: $('#amgp-access-configuration').serialize(),
            _wpnonce_amgp_save_kb_config: $('#_wpnonce_amgp_save_kb_config').val()
        };

        amgp_send_ajax( postData, amgp_callback_config_saved );
    });
    function amgp_callback_config_saved( response ) {
        $( '#amgp-user-tabs-section' ).html( response.message );
    }

	/** ***********************************************************************************************
	 *
	 *          AJAX calls
	 *
	 * **********************************************************************************************/


	// generic AJAX call handler
	function amgp_send_ajax( postData, refreshCallback, reload ) {

		var errorMsg;
		var theResponse;
		var refreshCallback = (typeof refreshCallback === 'undefined') ? 'amgp_callback_noop' : refreshCallback;

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: ajaxurl,
			beforeSend: function (xhr)
			{
				$('#amgp-ajax-in-progress').text('Working ...').dialog('open');
			}
		}).done(function (response)        {
			theResponse = ( response ? response : '' );
			if ( theResponse.error || typeof theResponse.message === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				errorMsg = theResponse.message ? theResponse.message : amgp_admin_notification('', amgp_vars.reload_try_again, 'error');
			}

		}).fail( function ( response, textStatus, error )        {
			//noinspection JSUnresolvedVariable
			errorMsg = ( error ? ' [' + error + ']' : amgp_vars.unknown_error );
			//noinspection JSUnresolvedVariable
			errorMsg = amgp_admin_notification(amgp_vars.error_occurred + '. ' + amgp_vars.msg_try_again, errorMsg, 'error');
		}).always(function ()        {
			$('#amgp-ajax-in-progress').dialog('close');

			if ( errorMsg ) {
				amgp_container.find( '.amag-ajax-loading-saving-icon' ).remove();

				$('.eckb-bottom-notice-message').replaceWith(errorMsg);
			} else {
				//Complete Spinner animation.
				amgp_loading_saving_icon_complete();

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

	function amgp_callback_noop( response ){}

	// Load Ajax Loading / Saving Icon
	function amgp_loading_saving_icon(){

		//If Loading Icon already exists then don't bother loading another one.
		if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
			return;
		}
		amgp_container.find( '.amag-ajax-loading-saving-icon' ).remove();

		var html = '<div class="amag-ajax-loading-saving-icon">' +
			'<div class="amag-loading-spinner"></div>' +
			'</div>';//amag-ajax-loading-saving-icon
		amgp_container.append( html );

	}

	// Show Success Checkbox inside Icon then remove it after set time.
	function amgp_loading_saving_icon_complete(){

		//Only run the completed icon if the loading icon exists.
		if( $( '.amag-ajax-loading-saving-icon').length  !== 0 ){
			amgp_container.find( '.amag-loading-spinner').after( '<div class="epkbfa epkbfa-check-circle"></div>');
			amgp_container.find( '.amag-loading-spinner' ).remove();

			//postpone then remove:
			var amgp_loading_timeout;
			clearTimeout(amgp_loading_timeout);

			//Add fadeout class to notice after set amount of time has passed.
			amgp_loading_timeout = setTimeout(function () {
				amgp_container.find( '.amag-ajax-loading-saving-icon' ).remove();
			} , 1000);
		}
	}


	/** ***********************************************************************************************
	 *
	 *          Pop Ups and ckeckboxes
	 *
	 * **********************************************************************************************/

	// SHOW INFO MESSAGES
	function amgp_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'<div class="amgp-close-notice epkbfa epkbfa-window-close"></div>'+
			'</div>';
	}

    /*
        HTML Popup box in the middle of the page that is generated if called.
        $values:
        @param: string $value['id']            ( Required ) Container ID, used for targeting with other JS
        @param: string $value['type']          ( Required ) How it will look ( amgp-error = Red  )
        @param: string $value['header']        ( Required ) The big Bold Main text
        @param: HTML   $value['content']       ( Required ) Any HTML P, List etc...
        @param: array  $value['checkboxes']    ( Optional ) Pass in an array of text labels.
     */
    function amg_confirmation_box_with_checkboxes( $args ) {
        var html = '';

        html += '<div id="' + $args['id'] + '" class="amag-notification-box amag-popup">';
        html += '<form class="' + $args['type'] + '">';

        html += '<section class="amag-header"><h3>' + $args['header'] + '</h3></section>';

        html += '<section class="amag-body">';
        if ( $args['content'] ) {
            html += '<div class="amag-body-content">' + $args['content'] + '</div>';
        }
        if ( $args['checkboxes'] ) {
            html += '<div class="amag-body-checkboxes">';

            var arrayLength = $args['checkboxes'].length;

            html += '<ul>';
            for ( var i = 0; i < arrayLength; i++ ) {
                html += '<li><label for="checkbox_' + i + '">' + $args['checkboxes'][i] + '</label><input type="checkbox" name="checkbox_' + i + '" id="checkbox_' + i + '" required></li>';
            }
            html += '</ul>';

            html += '</div>';
        }
        html += '</section>';

        html += '<section class="amgp-footer">';
        html += '<button type="button" id="amag-notification-box-apply-js" class="amag-error-btn">' + $args['apply_button'] + '</button>';
        if ( $args['cancel_button'] ) {
            html += '<button type="button" id="amag-notification-box-cancel" class="amag-success-btn">' + $args['cancel_button'] + '</button>';
        }
        html += '</section>';

        html += '</form>';
        html += '</div>';//.amag-notification-box

        $( '#amag-access-manager-container' ).append( html );

        $args['apply_function']($args['kb_group_name'], $args['kb_group_id']);

        //Remove Popup if cancel clicked on.
        amgp_container.find( '.amag-popup' ).on( 'click', '#amag-notification-box-cancel', function(e) {
            e.preventDefault();
            $( '.amag-popup' ).remove();
        } );

     }

    //Show or Hide checkboxes and submit button for Notification box on toggle button click.
    amgp_container.find( '.amag-notification-box' ).on( 'click', '.amag-content-toggle', function(){

        $( this ).parents( '.amag-collapse').find( '.amag-body-checkboxes').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.amgp-footer').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.fa').toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up');
        $( this ).parents( '.amag-collapse').toggleClass( 'amag-active-toggle' );
    });

    //Show or hide Toggle Content when title is clicked.
    amgp_container.on( 'click', '.amgp-toggle-box-header', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amgp-toggle-closed amgp-toggle-open ' );
        //Switch Icon pointer
        $( this ).find( '.amgp-toggle-box-icon' ).toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up ' );
    });

    //Show or hide Info Box Content when Icon is clicked.
    amgp_container.on( 'click', '.amgp-info-box-icon', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amgp-info-toggle-closed amgp-info-toggle-open ' );

    });
    //Close Info Box Content when X Icon is clicked.
    amgp_container.on( 'click', '.amgp-info-box-close', function(){

        //Hide Container
        $( this ).parents( '.amgp-info-box' ).toggleClass( 'amgp-info-toggle-closed amgp-info-toggle-open ' );

    });

    //Expand Info Box Content when Expand Icon is clicked.
    amgp_container.on( 'click', '.amgp-info-box-icon-max', function(){

        //Hide Container
        $( this ).parents( '.amgp-info-box' ).addClass( 'amgp-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amgp-info-box-icon-min' ).show();
    });
    //Compress Info Box Content when Compress Icon is clicked.
    amgp_container.on( 'click', '.amgp-info-box-icon-min', function(){

        //Hide Container
        $( this ).parents( '.amgp-info-box' ).removeClass( 'amgp-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amgp-info-box-icon-max' ).show();
    });
});