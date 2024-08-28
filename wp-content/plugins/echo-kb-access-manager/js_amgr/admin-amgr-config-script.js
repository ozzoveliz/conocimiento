jQuery(document).ready(function($) {

    var amgr_container = $( '#amag-access-manager-container' );
    var amgr_config_container =  $('#amgr-config-container');


    /** ***********************************************************************************************
     *
     *          PAGE CONTROLS
     *
     * **********************************************************************************************/

    /* Top Nav -----------------------------------------------------------------*/
    // Toggles the top Navigation ( KB, Config, Access ) to active status and displays their content.
    amgr_container.find( '#amgr-config-main-nav' ).on( 'click', '.amag-nav-button', function(){

        //If Loading Icon is running don't allow for nav to change.
        if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
            return;
        }

	    //Top Nav toggle Active classes
	    $( '.amgr-nav-section' ).removeClass( 'amgr-active-nav' );
	    $( this ).addClass( 'amgr-active-nav' );

        //Toggle Content based on Top nav
        $( '.amag-config-content').removeClass( 'amag-active-content' );
       var navID = $( this ).attr( 'id' );
       $( '#amgr-config-container').find( '#' + navID + '-content' ).addClass( 'amag-active-content' );

        // load appropriate content
        if ( navID === 'amgr-categories' ) {
	        amgr_loading_saving_icon();
	        amgr_load_access_tab_content_categories();
        } else if ( navID === 'amgr-articles' ) {
	        amgr_loading_saving_icon();
	        amgr_load_access_tab_content_articles();
        } else if ( navID === 'amgr-KB' ) {
          amgr_loading_saving_icon();
          amgr_load_access_tab_content_kb();
		}
    });

    // KBs DROPDOWN - Reload on change
    $( '#amgr-list-of-kbs' ).on( 'change', function(e) {
        amgr_loading_saving_icon();

        // var what = e.target.value;
        var kb_admin_url = $(this).find(":selected").data('kb-admin-url');
        if ( kb_admin_url ) {
            window.location.href = kb_admin_url;
        }
    });

    /* Tabs --------------------------------------------------------------------*/
    // Toggles Tab Navigation and it's content ( Users , Content )
    amgr_container.on( 'click', '.amag-nav-tabs li', function(){

        //If Loading Icon is running don't allow for tab nav to change.
        if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
            return;
        }

        var tabID = $( this ).attr( 'id' );

        //Reset all Tabs/Panels
        $( this ).parent().find( 'li' ).removeClass( 'amag-active-tab' );
        $( this ).parent().parent().find( '.amag-tab-panel' ).removeClass( 'amag-active-panel' );

        //Set Tab to active
        $( this ).addClass( 'amag-active-tab' );

        //Set Panel to active
        $( '#'+tabID+'-panel').addClass( 'amag-active-panel');
    });

    /* Clear Logs */
    amgr_container.on( 'click', '#amgr_reset_logs_ajax', function(){

        var postData = {
            action: 'amgr_reset_logs_ajax',
			_wpnonce_epkb_ajax_action: $('[name=_wpnonce_epkb_ajax_action]').val()
        };

        amgr_send_ajax( postData, null, true );
    });


	/** ***********************************************************************************************
	 *
	 *          ACCESS CONTENT: KB
	 *
	 * **********************************************************************************************/

	function amgr_load_access_tab_content_kb() {

		var postData = {
			action: 'amgr_display_kb_kbs_access_ajax',
			amag_kb_id: $('#amag_kb_id').val(),
			_wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax' ).val()
		};

		amgr_loading_saving_icon();

		amgr_send_ajax( postData, amgr_switch_display_access_content );
	}

	// Load Access tab
	function amgr_switch_display_access_content( response ) {
		$( '#amgr-KB-content' ).html();
		$( '#amgr-KB-content' ).html( response.message );
		$('.amag-config-content').hide();
		$('#amgr-KB-content').show();
	}


	// SAVE AMGR configuration
	$( 'body' ).on( 'click', '#epkb_save_amgr_settings', function (e) {
		e.preventDefault();  // do not submit the form

		var postData = {
			action: 'epkb_save_amgr_settings',
			_wpnonce_epkb_ajax_action: amgr_vars.nonce,
			amag_kb_id: $('#amag_kb_id').val(),
			epkb_prefix_enabled: $('#show_private_article_prefix').is(':checked'),
			form: $('#epkb-access-config').serialize()
		};

		amgr_loading_saving_icon();

		amgr_send_ajax( postData, amgr_config_saving_complete );
	});

	function amgr_config_saving_complete() {
		amgr_loading_saving_icon_complete();
	}


	/** ***********************************************************************************************
	 *
	 *          ACCESS CONTENT: Articles
	 *
	 * **********************************************************************************************/

	// 1. Load Category access based on selected KB Group
	amgr_config_container.on( 'change', '#amgr-access-tabs-kb-group-list-articles', function() {
		amgr_load_access_tab_content_articles();
	});

	function amgr_load_access_tab_content_articles() {
		// ignore user selecting "choose group" option
		var kb_group_id = $('#amgr-access-tabs-kb-group-list-articles').find(":selected").val();

		var postData = {
			action: 'amgr_display_kb_articles_access_ajax',
			amgr_kb_group_id: kb_group_id,
			amag_kb_id: $('#amag_kb_id').val(),
			_wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax' ).val()
		};

		amgr_loading_saving_icon();

		amgr_send_ajax( postData, amgr_switch_display_access_content_articles );
	}

	// Load Access tab
	function amgr_switch_display_access_content_articles( response ) {
		$( '#amgr-articles-content' ).html();
		$( '#amgr-articles-content' ).html( response.message );
		$('.amag-config-content').hide();
		$('#amgr-articles-content').show();
	}

	// SAVE Category Access action
	amgr_config_container.on( 'click', '#amgr_save_articles_access_ajax', function(e) {
		e.preventDefault();

		var kb_group_id =  $(this).parent().parent().find('#amgr_kb_group_id_article_access').val();
		if ( kb_group_id === '' ) {
			return;
		}

		amgr_loading_saving_icon();

		// retrieve all articles
		var article_data = [];
		$('#amgr-read-only-article-access').find('.amgr-article-read-only-access').each(function(i, obj) {

			// some layouts like Tabs Layout has top articles and sub-articles "disconnected". Connect them here
			article_data.push( $(this).val() + '=' + $(this).is(':checked') );
		});

		amgr_level_articles_access_operation( 'amgr_save_articles_access_ajax', kb_group_id, article_data, amgr_callback_access_articles_action );
	});

	// Call back-end for Access operations
	function amgr_level_articles_access_operation( operation, kb_group_id, article_data, callback ) {

		var postData = {
			action: operation,
			amag_kb_id: $('#amag_kb_id').val(),
			amgr_kb_group_id: kb_group_id,
			amgr_article_data: article_data,
			_wpnonce_epkb_ajax_action: amgr_vars.nonce
		};

		amgr_send_ajax( postData, callback );
	}
	function amgr_callback_access_articles_action() {
		amgr_load_access_tab_content_articles();
	}


    /** ***********************************************************************************************
     *
     *          ACCESS CONTENT: Categories
     *
     * **********************************************************************************************/

    // 1. Load Category access based on selected KB Group
    amgr_config_container.on( 'change', '#amgr-access-tabs-kb-group-list', function() {
        amgr_load_access_tab_content_categories();
    });

    function amgr_load_access_tab_content_categories() {
        // ignore user selecting "choose group" option
        var kb_group_id = $('#amgr-access-tabs-kb-group-list').find(":selected").val();

        var postData = {
            action: 'amgr_display_kb_category_access_ajax',
            amgr_kb_group_id: kb_group_id,
            amag_kb_id: $('#amag_kb_id').val(),
            _wpnonce_amar_access_content_action_ajax: $('#_wpnonce_amar_access_content_action_ajax' ).val()
        };

        amgr_loading_saving_icon();

        amgr_send_ajax( postData, amgr_switch_display_access_content_categories );
    }

    // Load Access tab
    function amgr_switch_display_access_content_categories( response ) {
        $( '#amgr-categories-content' ).html();
        $( '#amgr-categories-content' ).html( response.message );
        $('.amag-config-content').hide();
        $('#amgr-categories-content').show();
    }

    //Highlight Edit Mode Boxes
    amgr_config_container.on( 'click', '.amgr-edit-mode-container input' , function(){
        amgr_config_container.find( '.amgr-edit-mode-container li' ).removeClass('amgr-edit-active-no amgr-edit-active-full amgr-edit-active-read' );
        var value = $( this ).val();
        $( this ).parent().addClass( 'amgr-edit-active-'+value );
    });

    //Change Category Access on Click
    amgr_config_container.on( 'click', '#amgr-category-access-levels ul>li', function( e ){

		//do not trigger click event for parent Categories
		e.stopPropagation();

        //Get Edit Mode Value
        var editMode = $( 'input[name=amgr-edit-mode]:checked' ).val();
        var access = 'amgr-' + editMode + '-access';

        //Clear this Categories Access Classes
        clear_classes( this );

        //Update this Categories Access
        $( this ).addClass( access );
        $( this ).find( '>div input[type="hidden"]' ).attr( 'data-amgr-category-access-level', access );

        //Climb down the Tree if it's a "No Access", since children cannot be accessible if the parent is not.
        if( access === 'amgr-no-access' ){
            clear_classes( $( this ).find( 'li' ) );
            $( this ).find( 'li' ).addClass( access );
            $( this ).find( 'input[type="hidden"]' ).attr( 'data-amgr-category-access-level', access );
        }

        //Climb up the Tree and assign the same access level to it's parent categories if not already assigned an access type.
        //Get this Categories Parent values
       /* var parent_1 = $( this ).parent().find( 'input[type="hidden"]' ).attr( 'data-amgr-parent-level-1' );
        var parent_2 = $( this ).parent().find( 'input[type="hidden"]' ).attr( 'data-amgr-parent-level-2' );

        set_parent_access( parent_1, access );
        set_parent_access( parent_2, access ); */
    });

    //Clear Classes
    function clear_classes( element ){
        $( element ).removeClass( 'amgr-no-access amgr-read-access amgr-full-access ')
    }

    //Set Parent Access
    function set_parent_access( id , access ) {
        var parentID = '#'+id;

        //If Parent does not have already an access level assigned then assign it based on it's child's new access
        if( !$( parentID ).hasClass( 'amgr-read-access' ) && !$( parentID ).hasClass( 'amgr-full-access' ) ){
            //Update Classes
            $( parentID ).removeClass( 'amgr-no-access' );
            $( parentID ).addClass( access );
            //Set Hidden Input Value
            $( parentID ).find( 'input[type="hidden"]' ).attr( 'data-amgr-category-access-level', access );
        }
    }

	// SAVE Category Access action
	amgr_config_container.on( 'click', '#amgr_save_categories_access_ajax', function(e) {
		e.preventDefault();

		var kb_group_id =  $(this).parent().parent().find('.amgr_kb_group_id').val();
		if ( kb_group_id === '' ) {
			return;
		}

		amgr_loading_saving_icon();

		// retrieve all categories
		var category_data = [];
		$('#amgr-category-access-levels').find('[data-amgr-category-id]').each(function(i, obj) {

			// some layouts like Tabs Layout has top categories and sub-categories "disconnected". Connect them here
			category_data.push( $(this).data('amgr-category-id') + '=' + $(this).data('amgr-category-access-level') );
		});

		amgr_level_access_operation( 'amgr_save_categories_access_ajax', kb_group_id, category_data, amgr_callback_access_categories_action );
	});

	// Call back-end for Access operations
	function amgr_level_access_operation( operation, kb_group_id, category_data, callback ) {

		var postData = {
			action: operation,
			amag_kb_id: $('#amag_kb_id').val(),
			amgr_kb_group_id: kb_group_id,
			amgr_category_data: category_data,
			_wpnonce_epkb_ajax_action: amgr_vars.nonce
		};

		amgr_send_ajax( postData, callback );
	}
	function amgr_callback_access_categories_action() {
		amgr_load_access_tab_content_categories();
	}


	/** ***********************************************************************************************
     *
     *          Other
     *
     * **********************************************************************************************/

    // SAVE Access Manager configuration
    $('#amgr-access-save-configuration-container').on( 'click', '#amgr_save_kb_config', function(e) {
        e.preventDefault();

        amgr_loading_saving_icon();

        var postData = {
            action: 'amgr_save_kb_config_changes',
            amag_kb_id: $('#amag_kb_id').val(),
            form: $('#amgr-access-configuration').serialize(),
            _wpnonce_amgr_save_kb_config: $('#_wpnonce_amgr_save_kb_config').val()
        };

        amgr_send_ajax( postData, amgr_callback_config_saved );
    });
    function amgr_callback_config_saved( response ) {
        $( '#amgr-user-tabs-section' ).html( response.message );
    }


	/** ***********************************************************************************************
	 *
	 *          AJAX calls
	 *
	 * **********************************************************************************************/

	// cleanup after Ajax calls
	var amgr_timeout;
	$(document).ajaxComplete(function () {
		clearTimeout(amgr_timeout);

		//Add fadeout class to notice after set amount of time has passed.
		amgr_timeout = setTimeout(function () {
			var bottom_message = amgr_container.find('.eckb-bottom-notice-message');
			if ( bottom_message.length ) {
				bottom_message.addClass('fadeOutDown');
			}
		} , 10000);

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
				amgr_container.find( '.amag-ajax-loading-saving-icon' ).remove();

				$('.eckb-bottom-notice-message').replaceWith(errorMsg);
			} else {
				//Complete Spinner animation.
				amgr_loading_saving_icon_complete();

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

	function amgr_callback_noop( response ){}

	// Load Ajax Loading / Saving Icon
	function amgr_loading_saving_icon(){

		//If Loading Icon already exists then don't bother loading another one.
		if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
			return;
		}
		amgr_container.find( '.amag-ajax-loading-saving-icon' ).remove();

		var html = '<div class="amag-ajax-loading-saving-icon">' +
			'<div class="amag-loading-spinner"></div>' +
			'</div>';//amag-ajax-loading-saving-icon
		amgr_container.append( html );

	}

	// Show Success Checkbox inside Icon then remove it after set time.
	function amgr_loading_saving_icon_complete(){

		//Only run the completed icon if the loading icon exists.
		if( $( '.amag-ajax-loading-saving-icon').length  !== 0 ){
			amgr_container.find( '.amag-loading-spinner').after( '<div class="epkbfa epkbfa-check-circle"></div>');
			amgr_container.find( '.amag-loading-spinner' ).remove();

			//postpone then remove:
			var amgr_loading_timeout;
			clearTimeout(amgr_loading_timeout);

			//Add fadeout class to notice after set amount of time has passed.
			amgr_loading_timeout = setTimeout(function () {
				amgr_container.find( '.amag-ajax-loading-saving-icon' ).remove();
			} , 1000);
		}
	}


	/** ***********************************************************************************************
	 *
	 *          Pop Ups and ckeckboxes
	 *
	 * **********************************************************************************************/

	// SHOW INFO MESSAGES
	function amgr_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-bottom-notice-message eckb-bottom-notice-message--' + $type + '">' +
					'<div class="eckb-bottom-notice-message__header">'+
					'<div class="eckb-bottom-notice-message__header__title">'+( $title ? $title : $message )+'</div>'+
					'<div class="eckb-bottom-notice-message__header__close epkb-close-notice epkbfa epkbfa-window-close"></div>'+
					' </div>'+
					(!$title ? '' : '<div class="contents">' +$message+'</div>')+
			'</div>';
	}

    /*
        HTML Popup box in the middle of the page that is generated if called.
        $values:
        @param: string $value['id']            ( Required ) Container ID, used for targeting with other JS
        @param: string $value['type']          ( Required ) How it will look ( amag-error = Red  )
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

        html += '<section class="amag-footer">';
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
        amgr_container.find( '.amag-popup' ).on( 'click', '#amag-notification-box-cancel', function(e) {
            e.preventDefault();
            $( '.amag-popup' ).remove();
        } );

     }

    //Show or Hide checkboxes and submit button for Notification box on toggle button click.
    amgr_container.find( '.amag-notification-box' ).on( 'click', '.amag-content-toggle', function(){

        $( this ).parents( '.amag-collapse').find( '.amag-body-checkboxes').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.amag-footer').fadeToggle(100);
        $( this ).parents( '.amag-collapse').find( '.fa').toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up');
        $( this ).parents( '.amag-collapse').toggleClass( 'amag-active-toggle' );
    });

    //Show or hide Toggle Content when title is clicked.
    amgr_container.on( 'click', '.amgr-toggle-box-header', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amgr-toggle-closed amgr-toggle-open ' );
        //Switch Icon pointer
        $( this ).find( '.amgr-toggle-box-icon' ).toggleClass( 'fa-arrow-circle-down fa-arrow-circle-up ' );
    });

    //Show or hide Info Box Content when Icon is clicked.
    amgr_container.on( 'click', '.amgr-info-box-icon', function(){

        //Change Closed to Open Class
        $( this ).parent().toggleClass( 'amgr-info-toggle-closed amgr-info-toggle-open ' );

    });
    //Close Info Box Content when X Icon is clicked.
    amgr_container.on( 'click', '.amgr-info-box-close', function(){

        //Hide Container
        $( this ).parents( '.amgr-info-box' ).toggleClass( 'amgr-info-toggle-closed amgr-info-toggle-open ' );

    });

    //Expand Info Box Content when Expand Icon is clicked.
    amgr_container.on( 'click', '.amgr-info-box-icon-max', function(){

        //Hide Container
        $( this ).parents( '.amgr-info-box' ).addClass( 'amgr-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amgr-info-box-icon-min' ).show();
    });
    //Compress Info Box Content when Compress Icon is clicked.
    amgr_container.on( 'click', '.amgr-info-box-icon-min', function(){

        //Hide Container
        $( this ).parents( '.amgr-info-box' ).removeClass( 'amgr-info-max-size' );
        //Change this icon to compress icon
        $( this ).hide();
        $( this ).parent().find( '.amgr-info-box-icon-max' ).show();
    });
});