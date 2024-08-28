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
        $('#amgp_license_check').html('');

        var postData = {
            action: 'amgp_handle_license_request',
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
    license_form.on('click', '#amgp_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        var postData = {
            action: 'amgp_handle_license_request',
            amgp_license_key: $('#amgp_license_key').val().trim(),
            _wpnonce_amgp_license_key: $('#_wpnonce_amgp_license_key').val(),
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
               amgp_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : amgp_admin_notification('', 'KB Groups: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        amgp_admin_notification('', 'Please reload the page and try again (L37).', 'error');
            $('#amgp_license_check').html(output);

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = amgp_admin_notification('KB Groups: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

            amgp_loading_Dialog( 'remove', '' );
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
    function amgp_loading_Dialog( displayType, message ){

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


    /*********************************************************************************************
     *********************************************************************************************
     *
     *                MANAGE ARTICLES
     *
     * ********************************************************************************************
     ********************************************************************************************/

    // add active group to Article page search box
	$('#posts-filter .search-box').after( '<input type="hidden" id="amag_chosen_kb_group" name="amag_chosen_kb_group" value="' + $('#amag_chosen_kb_group').val() + '"/>');

    if ( $('.amgp-admin-list-page-chosen-group-container').length && $('#amag_chosen_kb_group').val() ) {
        var active_group = '&amag_chosen_kb_group=' + $('#amag_chosen_kb_group').val();

        $('.subsubsub').find('a').each(function() {
            if ( $(this).attr('href').match("^edit.php\\?") && $(this).attr('href').match(amgp_vars.match_article_search) ) {
                $(this).attr('href', $(this).attr('href') + active_group );
            }
        });

        $('#the-list').find('a').each(function() {
            if ( $(this).attr('href').match("^edit.php\\?") && $(this).attr('href').match(amgp_vars.match_article_search) ) {
                $(this).attr('href', $(this).attr('href') + active_group );
            }
        });

        //Insert Group Id
        $( '.inline-edit-private' ).after( '<input type="hidden" id="amag_chosen_kb_group" name="amag_chosen_kb_group" value="' + $('#amag_chosen_kb_group').val() + '"/>');
    }

    /* ARTICLE PAGE ---------------------------------------------------------------*/

    //Show Categories for Groups
    function show_categories_for_groups(){
        var categoryListItem = $( '.amgp-article-page-categories-container li' );
        var group_id = [];

        //Loop through each Group Checkbox
        $( '.amgp-article-page-group-container li input' ).each( function() {

            //Only collect the Group ID's of the checked boxes.
            if( $( this ).is( ':checked' ) ){
                group_id.push( parseInt( $( this ).val() ) );
            }
        });

        //Clear applied Classes
        categoryListItem.removeClass( 'amgp-show-group-cat' );
        $( '.amgp-group-icon-placeholder-container .amgp-group-icon-placeholder' ).removeClass( 'amgp-display-group-icon' );
        $( '.amgp-group-icon-placeholder-container' ).removeClass( 'amgp-hide-group-icons' );
        $( '.amgp-group-count' ).remove();

        //Find Categories that have the same data set and display them.
        categoryListItem.each( function() {

            //Collect Data
            var data = $( this ).data( 'amgp-group-id' );
	        if ( data === undefined ) {
		        return;
	        }
            //Group Data into Array
            var categoryGroupIds = JSON.parse( "[" + data + "]" );

            var arrayLength = group_id.length;
            //Loop through each Group ID and find matching Category ID's
            for ( var i = 0; i <= arrayLength; i++ ) {
                if( $.inArray( group_id[i], categoryGroupIds ) !== -1  ) {
                    $( this ).addClass( 'amgp-show-group-cat' );

                    //Loop through each icon and display the ones that match
                    $( '.amgp-group-icon-placeholder-container .amgp-group-icon-placeholder' ).each( function() {
                        //Collect Data
                        var dataID = $( this ).data( 'amgp-group-icon-id' );
                        if( parseInt( group_id[i] ) === parseInt( dataID ) ) {
                            $( this ).addClass( 'amgp-display-group-icon' );
                        }

                    });
                }
            }
        });

        //Loop through each container and if it has more then 2 Groups active then replace them with a number and a pop up.
	    $( '.amgp-group-icon-placeholder-container' ).each( function() {

		    var activeGroups = $( this ).find( '.amgp-display-group-icon' ).length;
		    if( activeGroups > 2 ) {
			    $( this ).append( '<span class="amgp-group-icon-placeholder amgp-group-count">'+activeGroups+'</span>' );
			    $( this ).addClass( 'amgp-hide-group-icons' );
		    }
	    });


    }

    //Don't allow Group to be unchecked if one of their Categories is checked.
    function prevent_group_deselect( input, e ){

        var checkedCount = 0;

        //Only collect the Group ID of the checked box.
        if( !$( input ).is( ':checked' ) ){
            var groupID = parseInt( $( input ).val() ) ;
        }
        //Loop through all Categories and find if any are checked and set a count
        $( '.amgp-article-page-categories-container li' ).each( function(){

            //Collect Data
            var data = $( this ).data( 'amgp-group-id' );
	        if ( data === undefined ) {
		        return;
	        }

            //Group Data into Array
            var categoryGroupIds=  JSON.parse( "[" + data + "]" );

            //If Group detected, check if the box is checked.
            if( $.inArray( groupID, categoryGroupIds ) !== -1  ) {

                if( $( this ).find( 'input' ).is( ':checked' ) ) {
                    checkedCount += 1;
                }
            }
        });

        //Prevent Un-check if categories are checked
        if( checkedCount > 0 ){
            //Prevent Un-check
            e.preventDefault();
            //Set Warning Message
            $( '.amgp-cannot-deselect-group' ).remove();
            $( '#amgp-article-page-inner-container' ).prepend( '<div class="amgp-cannot-deselect-group">Un-check assigned Categories</div>' );
            return false;
        }else{
            $( '.amgp-cannot-deselect-group' ).remove();
            return true;
        }

    }

    //If a checked Group has no Categories assigned show a message.
    function no_categories_assigned_to_group( input ){
        $( '.amgp-no-categories-detected' ).remove();

        //Only when checkbox is being checked
        if( $( input ).is( ':checked' ) ){
            if( $('.amgp-show-group-cat' ).length === 0 ){
                //Remove Select at least one Category message
                $( '.amgp-required-category' ).remove();
                //Add Message that this Group has no Categories assigned.
                $( '#amgp-article-page-inner-container' ).prepend( '<div class="amgp-no-categories-detected">No Categories Assigned</div>' );
            }
        }
    }

    //Don't allow any publishing until a KB Category has been checked
    function restrict_publishing() {
        if( $( '#amgp-group-article-access' ).length ) {

            $( '.amgp-required-category' ).remove();
            control_publish_area_display( 'hide' );
            $( '#amgp-article-page-inner-container' ).prepend( '<div class="amgp-required-category">' + amgp_vars.select_at_least_one_category + '</div>' );
            var error = 1;
            $( '.amgp-article-page-categories-container' ).find( ' li ' ).each( function () {

                //Go through each checkbox and check if checked. If not set error value
                if( $( this ).find( 'input[type="checkbox"]:checked' ).length ) {
                    error = 0;
                }
            });
            if( error === 0 ){
                control_publish_area_display( 'show' );
                $( '.amgp-required-category' ).remove();
            }
        }
    }

    //Show Message if no group has been selected.
	let no_group_message_timeout;
    function no_group_message() {

        if( $( '#amgp-group-article-access' ).length ) {

            $( '.amgp-required-group' ).remove();
            $( '.amgp-required-category' ).remove();

            //Only newest timeout handler should be running
            if ( no_group_message_timeout ) {
            	clearTimeout( no_group_message_timeout );
            }

            no_group_message_timeout = setTimeout( function (){

	            let error = 1;
	            $( '#amgp-article-page-inner-container' ).prepend( '<div class="amgp-required-group">' + amgp_vars.select_at_least_one_group + '</div>' );

	            $( '.amgp-article-page-group-container' ).find( ' li ' ).each( function () {

		            //Go through each checkbox and check if checked. If not set error value
		            if( $( this ).find( 'input[type="checkbox"]:checked' ).length ) {
			            error = 0;
		            }
	            });

	            //Make sure publishing area is hidden when no group is selected
	            control_publish_area_display( 'hide' );

	            //Groups are selected
	            if( error === 0 ){
		            $( '.amgp-required-group' ).remove();
		            restrict_publishing();
	            }

            }, 1000 );

        }
    }

    //Show / Hide Publish Area
    function control_publish_area_display( display_type ){

        if( display_type === 'show' ){

			//Gutenberg WordPress Editor
			editor = $( '.block-editor #editor' );
			if( editor.length > 0 ){
				wp.data.dispatch( 'core/editor' ).unlockPostSaving( 'amag' ); // unblock publish button
			} else { // Classic editor
				$('#publishing-action [type=submit]').prop('disabled', false);
			}
        } else if ( display_type === 'hide' ){

			//Gutenberg WordPress Editor
			editor = $( '.block-editor #editor' );
			if( editor.length > 0 ){
				if ( typeof wp.data != 'undefined' ) wp.data.dispatch( 'core/editor' ).lockPostSaving( 'amag' ) // lock publish button
			} else { // Classic editor
				$('#publishing-action [type=submit]').prop('disabled', 'disabled');
			}
        }
    }

	//Loop through each container and if it has more then 2 Groups active then replace them with a number and a pop up.
	$( '.amgp-group-icon-placeholder-container' ).each( function() {

		let activeGroups = $( this ).find( '.amgp-group-icon-placeholder' ).length;
		if( activeGroups > 2 ) {
			$( this ).append( '<span class="amgp-group-icon-placeholder amgp-group-count">'+activeGroups+'</span>' );
			$( this ).addClass( 'amgp-hide-group-icons' );
		}
	});

	//Show All active groups if Number Icon clicked on
	$( 'body' ).on( 'click', '.amgp-group-count', function() {
		$( this ).parent().toggleClass( 'amgp-hide-group-icons' );
	});

	//Initial script for None admin users
    function amgp_run_for_none_admin_users() {

	    control_publish_area_display( 'hide' );
	    no_group_message();
	    show_categories_for_groups();

	    //If only 1 Group, preselect the group
	    if( $( '.amgp-article-page-group-container li' ).length === 1 ){
		    $( '.amgp-article-page-group-container li input[type="checkbox"]' ).prop( "checked", true );
		    show_categories_for_groups();
	    }

	    //On input click , check Group Access
	    $( $( '#amgp-group-article-access' ).on( 'click' , 'input', function(e) {
		    if( prevent_group_deselect( $( this ), e ) ){
			    no_group_message();
			    no_categories_assigned_to_group( $( this ) );
		    }
	    }));

	    //Show Only Categories for the Group that is Selected in the Groups List.
	    $( $( '.amgp-article-page-group-container' ).on( 'click' , 'input', function(e) {
		    if( prevent_group_deselect( $( this ), e ) ){
			    show_categories_for_groups();
			    no_categories_assigned_to_group( $( this ) );
		    }
	    }));
    }

	//Initial script for admin users
	function amgp_run_for_admin_users() {

		//On input click , check for any categories that are checked.
		$( $( '#amgp-group-article-access' ).on( 'click' , 'input', function(e) {
			restrict_publishing();
		}));

		//Check and restrict publishing
		restrict_publishing();
	}

    //Run initial script - use delay to let all elements load
	let ampg_initial_check_count = 0;
	let amgp_initial_script_interval = setInterval( function() {

		//Let's try to initialize what we have if some of required elements are still missing after 5 seconds
		ampg_initial_check_count++;

		//Do not run initial script until all required elements loaded
		if (  ampg_initial_check_count >= 5
			|| ( $( '#amgp-group-article-access' ).length
				&& $( '#amgp-article-page-inner-container' ).length
				&& $( '.amgp-article-page-categories-container' ).length
				&& ( $( '#submitdiv' ).length || $( '.edit-post-header__settings' ).length ) ) ) {

			if( $( '.amgp-admin-categories' ).length === 1 ){
				amgp_run_for_admin_users();
			} else {
				amgp_run_for_none_admin_users();
			}

			clearInterval( amgp_initial_script_interval );
		}
	}, 1000 );


    /*********************************************************************************************
     *********************************************************************************************
     *
     *                MANAGE CATEGORIES
     *
     * ********************************************************************************************
     ********************************************************************************************/

    /* handle selection of KB Group on KB All Articles page */
    $('#wpbody').on('click', '[id^=amag_chosen_kb_group_article_switch_kb_choice_]', function (e) {
        e.preventDefault();  // do not submit the form
        amgp_loading_saving_icon();
        var postData = {
            action: 'amgp_switch_kb_group_on_article_page',
            _wpnonce_amgp_article_switch_kb: $('#_wpnonce_amgp_article_switch_kb').val(),
	        amgp_kb_id: $('#amgp_kb_id').val(),
            amag_chosen_kb_group: $(this).val()
        };

        send_ajax_request_switch_groups( 'POST', postData, 'Switching KB Group...' );
    });

    function send_ajax_request_switch_groups( ajax_type, postData, action_msg ) {

        var msg;

        $.ajax({
            type: ajax_type,
            dataType: 'json',
            data: postData,
            url: ajaxurl,
            beforeSend: function (xhr)
            {
                //noinspection JSUnresolvedVariable
                $('#amgp-ajax-in-progress').text(action_msg).dialog('open');
            }
        }).done(function (response) {

            response = ( response ? response : '' );
            if ( response.message || typeof response.amag_chosen_kb_group === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : amgp_admin_notification('', 'Access Manager: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            window.location.href = window.location.href + '&amag_chosen_kb_group=' + response.amag_chosen_kb_group;

        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = amgp_admin_notification('Access Manager: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

            $('#amgp-ajax-in-progress').dialog('close');
            if ( msg ) {
                $('.eckb-bottom-notice-message').replaceWith(msg);
                $( "html, body" ).animate( {scrollTop: 0}, "slow" );
            }
        });
    }

    // remove quick edit categories for now, because we need to fix the access per group as it shows all if user has more groups assigned.
	$('.wp-list-table.posts').on( 'click' ,'.editinline',function (){
		$('.wp-list-table.posts').find('.ep' + 'kb_post_type_1_category-checklist').each( function () {
            $(this).hide();
			$(this).after('<span>Quick edit of categories not available</span>');
		});
    });



    /* Dialogs --------------------------------------------------------------------*/

    // SAVE AJAX-IN-PROGRESS DIALOG
    function setup_ajax_in_progress_dialog() {
        $('#amgp-ajax-in-progress').dialog({
            resizable: false,
            height: 70,
            width: 200,
            modal: false,
            autoOpen: false
        }).hide();
    }

    // SHOW INFO MESSAGES
    function amgp_admin_notification( $title, $message , $type ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
            '</div>' +
            '</div>';
    }



    /* SHARED PAGES ------------------------------------------------------------*/

    //Move KB Group Radio Buttons above "Add New Category" text.
    //If Category page Groups container exists run code.
    if ( $( '.amgp-admin-list-page-chosen-group-container' ).length ) {

        // If checkbox checked show content below
        if( $('.amgp-radio-checked' ).length ){
            $('#col-container').show();
        }

        //Move KB Groups below Categories Title
        var groups_radio_buttons = $( '.amgp-admin-list-page-chosen-group-container' ).clone();
        $( '.amgp-admin-list-page-chosen-group-container' ).remove();
        $( '.wp-heading-inline' ).after( groups_radio_buttons );

        //Select Checkboxes when clicked on
    } else {
        $('#col-container').show();
    }
    //Initially we have the sidebar hidden until the page loads then slowly fade in the sidebar so that the elements are in the correct location and loaded.
    $( '.amag-private-admin' ).find('.amgp-admin-list-page-chosen-group-container').fadeIn();



    // Load Ajax Loading / Saving Icon
    function amgp_loading_saving_icon(){

        //If Loading Icon already exists then don't bother loading another one.
        if( $( '.amag-ajax-loading-saving-icon').length  > 0 ){
            return;
        }
        $( '.amag-private-admin' ).find( '.amag-ajax-loading-saving-icon' ).remove();

        var html = '<div class="amag-ajax-loading-saving-icon">' +
            '<div class="amag-loading-spinner"></div>' +
            '</div>';//amag-ajax-loading-saving-icon
        $( '.amag-private-admin' ).append( html );
    }

    //AMGP Group Selection for Preview Box
    ( function(){
        var group_selection = $( '#amgp-main-page-group-selection' );

        // Hide all content on page load
        $( '.amgp_kb_mode' ).hide();

        // On page load if KB Group selected then show its content
        if ( group_selection.val() ) {
            amgp_show_kb_group( group_selection.val() );
        }

        // Show selected Group KB
        $('body').on('change', '#amgp-main-page-group-selection', function (e) {
            amgp_show_kb_group( $( this ).val() );
        });

        function amgp_show_kb_group( kb_group_id ) {
            var group_kb = '#amgp_kb_section_group_id_' + kb_group_id;
            $( '.amgp_kb_mode' ).hide();
            $( group_kb ).show();
        }
    })();

    /* Clear Logs For Bottom Notice */
    $( 'body' ).on( 'click', '.amgp_notice_reset_logs_ajax', function(){
        var postData = {
            action: 'amgp_reset_logs_ajax',
            _wpnonce_epkb_ajax_action: amgr_vars.nonce
        };

        amgp_send_ajax( postData, null, true );
    });

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
