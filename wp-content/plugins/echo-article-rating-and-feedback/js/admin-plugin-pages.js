jQuery(document).ready(function($) {

    setup_ajax_in_progress_dialog();


    /*********************************************************************************************
     *********************************************************************************************
     *
     *                MANAGE LICENSE
     *
     * ********************************************************************************************
     ********************************************************************************************/

    let license_form = $('#ekcb-licenses');
    function check_license_status() {
        $('#eprf_license_check').html('');

        let postData = {
            action: 'eprf_handle_license_request',
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

    /* SAVE EPRF LICENSE; runs for just EPRF license field */
    license_form.on('click', '#eprf_save_btn', function (e) {
        e.preventDefault();  // do not submit the form

        let postData = {
            action: 'eprf_handle_license_request',
            eprf_license_key: $('#eprf_license_key').val().trim(),
	        _wpnonce_eprf_ajax_action: eprf_vars.nonce,
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
				eprf_loading_Dialog( 'show', action_msg );
            }
        }).done(function (response) {

            response = ( response ? response : '' );

			if ( typeof response.alert !== 'undefined' ) {
				alert( response.alert );
			}

            if ( response.message || typeof response.output === 'undefined' ) {
                //noinspection JSUnresolvedVariable,JSUnusedAssignment
                msg = response.message ? response.message : eprf_admin_notification('', 'Article Rating and Feedback: Error occurred. Please try again later. (L01)', 'error');
                return;
            }

            var output = typeof response.output !== 'undefined' && response.output ? response.output :
                                                                        eprf_admin_notification('', 'Please reload the page and try again (L37).', 'error');
            $('#eprf_license_check').html(output);


        }).fail(function (response, textStatus, error) {
            msg = ( error ? ' [' + error + ']' : 'unknown error' );
            msg = eprf_admin_notification('Article Rating and Feedback: Error occurred. Please try again later. (L02)', msg, 'error');
        }).always(function () {

			eprf_loading_Dialog( 'remove', '' );
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
	function eprf_loading_Dialog( displayType, message ){

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
        $('#eprf-ajax-in-progress').dialog({
            resizable: false,
            height: 70,
            width: 200,
            modal: false,
            autoOpen: false
        }).hide();
    }

    // SHOW INFO MESSAGES
    function eprf_admin_notification( $title, $message , $type ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
            '</div>' +
            '</div>';
    }

	/*********************************************************************************************
	 *********************************************************************************************
	 *
	 *                STATISTICS
	 *
	 * ********************************************************************************************
	 ********************************************************************************************/

	function showStatistics(stars) {

    	// If it's the Gutenberg Inspector sidebar, we need to set the statistic postion differently to deal with the scroll.
		// If Stats are on Gutenberg page run this script.
		if( $( '.eprf-ratings-gutenberg-editor-container' ).length > 0 ){

			// Get position of stars container, relative to the document.
			let starsContainerPosition = $( 'body .eprf-ratings-gutenberg-editor-container' ).offset();

			// Set the Statistics box position.
			let moduleStatisticsStyle = {
				"position" : "fixed",
				"top"      : starsContainerPosition.top+50,
			};
			
			if ( $('[dir=rtl]').length ) {
				moduleStatisticsStyle.left = '30px';
			} else {
				moduleStatisticsStyle.right = '30px';
			}
			
			$( 'body .eprf-ratings-gutenberg-editor-container' ).find( '.eprf-stars-module__statistics' ).css( moduleStatisticsStyle );
		}

		let statistisc = stars.closest('.eprf-stars-module').find('.eprf-stars-module__statistics');
		statistisc.show();

	}

	$(document.body).on('click', '.show-eprf-stars-module__statistics', function() {
		showStatistics($(this));
	});

	$(document.body).on('mouseleave', '.eprf-stars-module', function(){
		$('.eprf-stars-module .eprf-stars-module__statistics').hide();
	});

	// disable buttons in admin all articles column
	$(document.body).on('click', '.eprf_rating.column-eprf_rating *', false);

	// delete all meta from the article
	$(document.body).on('click', '#resetArticleRating', function(){
		
		if ( typeof eprf_vars.nonce == 'undefined' ) {
			//should never run, added just in case
			alert( 'Missing EPRF rating data.' );
			return true;
		}
		
		if (confirm(eprf_vars.delete_confirm)) {
			// find article id
			let articleId = $('#post_ID').val();

			// delete comments
			let postData = {
				action: 'eprf_handle_reset_article_feedback',
				_wpnonce_eprf_ajax_action: eprf_vars.nonce,
				article_id: articleId
			};

			send_request( 'GET', postData, 'Wait please...' );

			// update page content
			$('.eprf-like-count, .eprf-dislike-count').text('0');
			$('.eprf-stars-container .epkbfa-star').removeClass('epkbfa-star').addClass('epkbfa-star-o');
			$('.eprf-stars-container .epkbfa-star-half-o').removeClass('epkbfa-star-half-o').addClass('epkbfa-star-o');
			$('.eprf-stars-module .eprf-stars-module__statistics h6').text('');
			$('.eprf-stars-module .eprf-stars-module__statistics table .eprf-stars-module__statistics__stat-wrap__stat-inside').css({'width' : '0%' });
			$('.eprf-stars-module .eprf-stars-module__statistics table tr td:last-child').text('0%');

		}

		return false;
	});

	// Show Statistics if toggle clicked on.
	$(document.body).on('click', '.eprf-show-statistics-toggle', function() {
		showStatistics($(this));
	});
});
