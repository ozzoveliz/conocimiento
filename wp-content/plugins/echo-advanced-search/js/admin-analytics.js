jQuery(document).ready(function($) {


	// Charts -----------------------------------------------------------------------/

	/**
	 * Displays Pie Chart from Chart.js Library using data pulled from the page.
	 * This Pie chart only accepts 10 Words.
	 *
	 * @param {string}   container_id       The top most container ID of this Box.
	 *
	 */
	function display_asea_pie_chart( container_id ){

		if( $( '.asea-analytics-container' ).length > 0 ){

			//Calculate First 10 list items. -------------------------------/
			$first_ten_count = [];
			$first_ten_words = [];
			total = $( '#'+container_id+ ' .asea-pie-data-list li' ).length;

			//Get Values from Data List that's beside the pie chart diagram. Pull the Words and the count number.
			$( '#'+container_id+ ' .asea-pie-data-list .asea-first-10' ).each( function () {
				$first_ten_count.push( $(this ).find( '.asea-pie-chart-count' ).text() );
				$first_ten_words.push( $(this ).find( '.asea-pie-chart-word' ).text() );
			});

			//Calculate Remaining list items.  -----------------------------/
			//If Show Data Class exists, then gather all remaining list item data.
			if( $( '#'+container_id ).hasClass( 'asea-pie-chart-container-show-data' ) ){
				$remaining_count = 0;
				$remaining_words = 'Remaining Results';

				//Count up the total number remaining results counts.
				$( '#'+container_id+ ' .asea-pie-data-list .asea-after-10' ).each( function () {
					$remaining_count+= Number( ( $(this ).find( '.asea-pie-chart-count' ).text() ) );
				});
				$first_ten_count.push( $remaining_count );
				$first_ten_words.push( $remaining_words );

			}
			//Display Total beside the title.
			$( '#'+container_id+ ' .asea-pie-data-total' ).remove();
			$( '#'+container_id+ ' h4' ).append( '<span class="asea-pie-data-total"> ( '+total+' )</span>' );

			//Clear Canvas Tag for new Pie chart.
			$( '#'+container_id+'-chart').remove();
			$( '#'+container_id+' .asea-pie-chart-right-col #asea-pie-chart').append('<canvas id="'+container_id+'-chart'+'"><canvas>');

			var ctx = document.getElementById( container_id+'-chart' ).getContext( '2d' );

			var aseaChart = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: $first_ten_words,
					datasets: [{
						data: $first_ten_count,
						backgroundColor: [
							'#aed581',
							'#4fc3f7',
							'#FED32F',
							'#ef5350',
							'#D0D8E0',
							'#ff8a65',
							'#ba68c8',
							'#4A7BEC',
							'#768CA3',
							'#8d6e63',
							'#444444',
						],
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false ,
						position: 'left',
					},
				}
			});
		}


	}

	$('.asea-pie-chart-container').each(function(){
		var id = $(this).attr('id');
		display_asea_pie_chart( id );
		display_asea_pie_chart( id );
	});

	// Top panel tabs
	$('.asea-analytics-content__tab-button').on( 'click', function(){
		$(this).parent().find('.asea-analytics-content__tab-button').removeClass('active');
		$(this).addClass('active');

		$(this).closest('.asea-analytics-content').find('.asea-analytics-content__tab').removeClass('active');
		$( $(this).data('target') ).addClass('active');

		return false;
	});

	// show/hide full Pie chart data
	if( $( '.asea-analytics-container' ).length > 0 ) {

		$( '.asea-search-data-content .asea-pie-chart__more-button' ).on( 'click', function() {

			// Get this containers ID
			const id = $( this ).closest( '.asea-pie-chart-container' ).attr( 'id' );

			// Toggle Class for the main container
			$( '#' + id ).toggleClass( 'asea-pie-chart-container-show-data' );

			// Toggle More/Less Button Class for the text
			$( `#${id} .asea-pie-chart__more-button__more-text` ).toggleClass('epkb-hidden');
			$( `#${id} .asea-pie-chart__more-button__less-text` ).toggleClass('epkb-hidden');

			// Show all Data
			display_asea_pie_chart( id );
		});

	}

	// reset button
	if ( $( '.asea-reset-container' ).length > 0 ){
		
		$( '.asea-reset-analytics' ).on( 'click',  function(){

			$( '.epkb-dialog-box-form' ).toggleClass( 'epkb-dialog-box-form--active' );
			return false;
		});
		
		$('#asea-reset-search-data .asea-accept-button').on( 'click', reset_analytics);

		$('#asea-reset-cancel').on( 'click', function(){
			$('#asea-ajax-in-progress').dialog('close');
		});
		
		function reset_analytics() {
			loadingDialog( 'Processing ...' );
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'asea_handle_reset_request',
					_wpnonce_asea_search_analytics : $('#_wpnonce_asea_search_analytics').val(),
					kb_id : $('#asea_reset_analytics_kb_id').val()
				},
				url: ajaxurl,
				beforeSend: function (xhr)
				{
					//$('#asea-ajax-in-progress').dialog('open');

				}
			}).done(function (response) {
				$( '.asea-dialog-box-loading' ).remove();

				response = ( response ? response : '' );
				if ( response.message && response.error !== 'true') {
					//noinspection JSUnresolvedVariable,JSUnusedAssignment
					msg = response.message ? asea_admin_notification('', response.message, 'success') : asea_admin_notification('', 'Advanced Search: Error occurred. Please try again later. (L01)', 'error');
					// $('.asea-pie-chart-container, .asea-statistics-container').slideUp('fast');
					window.location.reload();

				} else if ( response.message ) {
					msg = response.message ? response.message : asea_admin_notification('', 'Advanced Search: Error occurred. Please try again later. (L01)', 'error');
				}
				
			}).fail(function (response, textStatus, error) {
				msg = ( error ? ' [' + error + ']' : 'unknown error' );
				$( '.asea-dialog-box-loading' ).remove();
			}).always(function () {

				$('#asea-ajax-in-progress').dialog('close');
				$('.asea-dbf__footer__cancel__btn').trigger('click');
				$('#asea-reset-analytics-button').hide();
        
				if ( msg ) {
					if ($('.eckb-top-notice-message').length) {
						$('.eckb-top-notice-message').replaceWith(msg);
					} else {
						$('.epkb-config-wrapper').append(msg);
					}
				}
			});
		}
	}
	
	// SHOW INFO MESSAGES
    function asea_admin_notification( $title, $message , $type, $yes_id, $no_id ) {
        return '<div class="eckb-top-notice-message">' +
            '<div class="contents">' +
            '<span class="' + $type + '">' +
            ($title ? '<h4>'+$title+'</h4>' : '' ) +
            ($message ? $message : '') +
            '</span>' +
			($yes_id ? '<button id="">'+$title+'</h4>' : '' ) +
            '</div>' +
            '</div>';
    }


    // Close Dialog box
	$( '.asea-dbf__footer__cancel__btn, .asea-dbf__close' ).on('click', function(){
		$( this ).parents( '.asea-dialog-box-form' ).removeClass( 'asea-dialog-box-form--active' );
	});
	
	// SAVE AJAX-IN-PROGRESS DIALOG
    function setup_ajax_in_progress_dialog() {
        $('#asea-ajax-in-progress').dialog({
            resizable: false,
            modal: false,
            autoOpen: false
        }).hide();
    }
	
	$('body').on('click', '.asea-close-notice', function(){
		$(this).closest('.eckb-bottom-notice-message').remove();
	});



	/**
	  * Displays a Center Dialog box with a loading icon and text.
	  *
	  * This should only be used for indicating users that loading is in progress, nothing else.
	  *
	  * @param  String    message    Optional    Message output from database or settings.
	  *
	  * Outputs: Removes old dialogs and adds the HTML to the end body tag
	  *
	  */
	function loadingDialog( message ){

		// Remove any old dialogs.
		//$( '.epbl-admin-dialog-box-status' ).remove();
		$( '.asea-dialog-box-loading' ).remove();

		let output = '<div class="asea-dialog-box-loading">' +

			//<-- Header -->
			'<div class="asea-admin-dbl__header">' +
				'<div class="asea-admin-dbl-icon epkbfa epkbfa-hourglass-half"></div>'+
					(message ? '<h4>' + message + '</h4>' : '' ) +
				'</div>'+
			'</div>';

		//Add message output at the end of Body Tag
		$( '.asea-analytics-container' ).append( output );
	}

	/*************************************************************************************************
	 *
	 *          Other
	 *
	 ************************************************************************************************/

	// Submit analytics filters form after change date select field
	$( document.body ).on( 'change', '.asea_admin_analytics_filters select', function() {
		loadingDialog( 'Processing ...' );
		$( this ).closest( 'form' ).submit();
	});

});