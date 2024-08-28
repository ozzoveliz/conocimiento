jQuery(document).ready(function($) {

	setup_ajax_in_progress_dialog();

	if( $('#emkb-create-kb-btn').length ) {
		$('#ekb_core_top_heading').hide();
	}

	/* Create a new KB ------------------------------------------------------*/
	$("#emkb-create-kb-btn").on('click', function(e){ // TODO remove after Access Manager has Wizard
		e.preventDefault();
		emkb_create_kb.dialog('open');
	});
	/* Create a new KB ------------------------------------------------------*/
	$("#emkb-create-wizard-kb-btn").on('click', function(e){
		e.preventDefault();
		$("#emkb-add-kb").submit();
	});
	/* Create a new KB - new design -----------------------------------------*/
	$( '#epkb-list-of-kbs' ).on( 'change', function() {

		let selected_option = $( this ).find( 'option:selected' );

		// Do nothing for options that does not belong to this plugin
		if ( selected_option.attr( 'data-plugin' ) !== 'emkb' ) {
			return;
		}

		$( selected_option.attr( 'data-target' ) ).submit();
	});

	/* Drop down Tabs ------------------------------------------------------*/
	(function () {

		//Get the Title Name of the Active Drop Down Tab
		var $active_title = $('.drop_down_tabs .active').attr('title');

		//If $active_title Defined Create a copy on the Top Level Tabs
		if (typeof $active_title !== 'undefined')
		{
			$('.drop_down_tabs').before('' +
				'<li>' +
				'<a class="nav_tab active">' +
				'<span class="active-tab-text"> ' + $active_title + '</span>' +
				'</a>' +
				'</li>' +
				'');
		}

		//Show Hide Drop down KB's
		$( '.drop_down_tabs' ).on( 'click' , function (){

			$( this ).toggleClass( 'active-drop-down' );
			$( this ).find( 'ul' ).toggle();

		});

	})();

	/*********************************************************************************************
	 *********************************************************************************************
	 *
	 *                MANAGE LICENSE
	 *
	 * ********************************************************************************************
	 ********************************************************************************************/
	var license_form = $('#ekcb-licenses');

	function check_license_status() {
		$('#emkb_license_check').html('');

		var postData = {
			action: 'emkb_handle_license_request',
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

	/* SAVE EMKB LICENSE; runs for just EMKB license field */
	license_form.on('click', '#emkb_save_btn', function (e) {
		e.preventDefault();  // do not submit the form

		var postData = {
			action: 'emkb_handle_license_request',
			emkb_license_key: $('#emkb_license_key').val().trim(),
			_wpnonce_emkb_license_key: $('#_wpnonce_emkb_license_key').val(),
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
				emkb_loading_Dialog( 'show', action_msg );
			}
		}).done(function (response) {

			response = ( response ? response : '' );
			if ( response.message || typeof response.output === 'undefined' ) {
				//noinspection JSUnresolvedVariable,JSUnusedAssignment
				msg = response.message ? response.message : emkb_admin_notification('', 'Multiple Knowledge Bases: Error occurred. Please try again later. (L01)', 'error');
				return;
			}

			var output = typeof response.output !== 'undefined' && response.output ? response.output :
				emkb_admin_notification('', 'Please reload the page and try again (L37).', 'error');
			$('#emkb_license_check').html(output);

		}).fail(function (response, textStatus, error) {
			msg = ( error ? ' [' + error + ']' : 'unknown error' );
			msg = emkb_admin_notification('Multiple Knowledge Bases: Error occurred. Please try again later. (L02)', msg, 'error');
		}).always(function () {

			emkb_loading_Dialog( 'remove', '' );
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
	function emkb_loading_Dialog( displayType, message ){

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
		$('#emkb-ajax-in-progress').dialog({
			resizable: false,
			height: 70,
			width: 200,
			modal: false,
			autoOpen: false
		}).hide();
	}

	// CREATE NEW KB DIALOG  TODO remove after Access Manager has Wizard
	var emkb_create_kb = $("#emkb-dialog-create-kb").dialog(
		{
			resizable: false,
			autoOpen: false,
			modal: true,
			buttons: {
				Create: function ()
				{
					$('#emkb_kb_name_input').val( $('#emkb_kb_name').val() );
					$('#emkb_kb_slug_input').val( $('#emkb_kb_slug').val() );
					$( this ).dialog( "close" );
					$("#emkb-add-kb").submit();
				},
				Cancel: function ()
				{
					$( this ).dialog( "close" );
				}
			},
			close: function ()
			{
			}
		}
	);

	// SHOW INFO MESSAGES
	function emkb_admin_notification( $title, $message , $type ) {
		return '<div class="eckb-top-notice-message">' +
			'<div class="contents">' +
			'<span class="' + $type + '">' +
			($title ? '<h4>'+$title+'</h4>' : '' ) +
			($message ? $message : '') +
			'</span>' +
			'</div>' +
			'</div>';
	}

	//IF Gutenberg is detected , run script below
	if( $( '.block-editor' ).length > 0 ) {

		//Grab the KB Tabs html and move it inside the Gutenberg Header after the Toolbar.
		//timing the script so that it allows Gutenberg to first load so that we can target it's elements.
		setTimeout(function () {

			//Copy Our KB tabs HTML.
			kb_tabs_html = $( "#ekb_core_top_heading" ).clone();

			//Remove old Tab
			$( "#ekb_core_top_heading" ).remove();

			//Insert KB Tabs into Header after Toolbar.
			$(".edit-post-header-toolbar").after( kb_tabs_html );

			//Add KB Tab Mobile Icon
			$( '.tab_navigation' ).before( '<span class="epk-tab-nav-mobile-icon dashicons dashicons-welcome-learn-more"></span>');

			//Get active tab Text
			var kb_name = $( '.tab_navigation .active span' ).text();

			//Add Active KB name beside Icon
			$( '#ekb_core_top_heading' ).append( '<span class="epk-tab-active-kb">'+kb_name+'</span>' );

			//Mobile Tab Toggle
			$( '.epk-tab-nav-mobile-icon' ).on( 'click', function(){
				$( this ).toggleClass( 'ep' + 'kb-active-tab-icon' );
				$( this ).parents().find( '.tab_navigation' ).toggle();
			});

			// Move More tab lists to the main list.
			$( '.drop_down_tabs ul li' ).each( function(){

				li = $( this ).clone();
				$( '.tab_navigation' ).append( li );

			});

			//Add Arrow above drop down list
			$( '.tab_navigation' ).prepend( '<i class="ekb-tab-nav-arrow-icon epkbfa epkbfa-sort-asc" aria-hidden="true"></i>' )

		}, 1000 );

	}
	
	// delete KB dialog 
	$('.emkb-delete-kbs .error-btn').on( 'click', function(){
		$(this).closest('.emkb-delete-kbs').find( '.emkb-dialog-box-form' ).toggleClass( 'emkb-dialog-box-form--active' );
		return false;
	});
	
	$('.emkb-dbf__close, .emkb-dbf__footer__cancel__btn').on( 'click', function(){
		$(this).closest( '.emkb-dialog-box-form' ).toggleClass( 'emkb-dialog-box-form--active' );
	});
	
	$('.emkb-delete-kbs .emkb-dbf__footer__accept__btn').on( 'click', function(){
		if ( $(this).closest('form').length ) {
			$(this).closest('form').submit();
		}
	});
});
