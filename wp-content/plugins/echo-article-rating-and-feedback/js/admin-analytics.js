jQuery(document).ready(function($) {


	// Charts -----------------------------------------------------------------------/

	/**
	 * Displays Pie Chart from Chart.js Library using data pulled from the page.
	 * This Pie chart only accepts 10 Words.
	 *
	 * @param {string}   container_id       The top most container ID of this Box
	 *
	 */
	function display_eprf_pie_chart( container_id ) {

		if( $( '.eprf-analytics-container' ).length > 0 ){

			//Calculate First 10 list items. -------------------------------/
			$first_ten_count = [];
			$first_ten_words = [];
			total = $( '#'+container_id+ ' .eprf-pie-data-list .eprf-first-10' ).length;

			//Get Values from Data List that's beside the pie chart diagram. Pull the Words and the count number.
			$( '#'+container_id+ ' .eprf-pie-data-list .eprf-first-10' ).each( function () {
				$first_ten_count.push( $(this ).find( '.eprf-pie-chart-count' ).text() );
				$first_ten_words.push( $(this ).find( '.eprf-pie-chart-word' ).text() );
			});

			//Calculate Remaining list items.  -----------------------------/
			//If Show Data Class exists, then gather all remaining list item data.
			if( $( '#'+container_id ).hasClass( 'eprf-pie-chart-container-show-data' ) ){
				$remaining_count = 0;
				$remaining_words = 'Remaining Results';
				total = $( '#'+container_id+ ' .eprf-pie-data-list li' ).length;

				//Count up the total number remaining results counts.
				$( '#'+container_id+ ' .eprf-pie-data-list .eprf-after-10' ).each( function () {
					$remaining_count+= Number( ( $(this ).find( '.eprf-pie-chart-count' ).text() ) );
				});
				$first_ten_count.push( $remaining_count );
				$first_ten_words.push( $remaining_words );

			}

			//Display Total beside the title.
			$( '#'+container_id+ ' .eprf-pie-data-total' ).remove();
			$( '#'+container_id+ ' h4' ).append( '<span class="eprf-pie-data-total"> ( '+total+' )</span>' );

			//Clear Canvas Tag for new Pie chart.
			$( '#'+container_id+'-chart').remove();
			$( '#'+container_id+' .eprf-pie-chart-right-col #eprf-pie-chart').append('<canvas id="'+container_id+'-chart'+'"><canvas>');

			var ctx = document.getElementById( container_id+'-chart' ).getContext( '2d' );

			var eprfChart = new Chart(ctx, {
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

	display_eprf_pie_chart( 'eprf-popular-ratinges-data' );
	display_eprf_pie_chart( 'eprf-no-result-popular-ratinges-data' );

	// show/hide full Pie chart data
	if( $( '.eprf-analytics-container' ).length > 0 ) {

		$( '#eprf-rating-data-content .eprf-pie-chart__more-button' ).on( 'click', function() {

			// Get this containers ID
			const id = $( this ).closest( '.eprf-pie-chart-container' ).attr( 'id' );

			// Toggle Class for the main container
			$( '#' + id ).toggleClass( 'eprf-pie-chart-container-show-data' );

			// Toggle More/Less Button Class for the text
			$( `#${id} .eprf-pie-chart__more-button__more-text` ).toggleClass('epkb-hidden');
			$( `#${id} .eprf-pie-chart__more-button__less-text` ).toggleClass('epkb-hidden');

			// Show all Data
			display_eprf_pie_chart( id );
		});

	}

	$(document.body).on('click', '#eprfUpdateReports', function(){});
	
	$(document.body).on('click', '.report-radio', function(){
		var wrap = $(this).closest('.rating-table-wrap');
		var curr = wrap.find('.report-radio input:checked').val();
		
		curr = parseInt(curr);
		
		$i = 0;
		wrap.find('tr').each(function(){
			$i++;
			if (curr == 10 && $i > 11) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});
			
	});
});