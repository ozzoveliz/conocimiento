
jQuery(document).ready(function ($) {

	$("#asea_advanced_search_terms").echo_autocomplete({
		delay: asea_vars.advanced_search_auto_complete_wait,
		source: asea_live_search,
		minLength: 1
	});

	$("#asea-search-filter-clear-results").on('click', function () {
		$(".asea-search-filter-container .asea-filter-option-input").prop('checked', false);
		$("#asea_advanced_search_terms").val('');
		$('#asea_search_results').css('display', 'none');
	});
	
	$(document).on( 'keyup', function(e) {
		if (e.key === "Escape" && $('#asea_search_results').css('display') !== 'none' ) { 
			$('#asea_search_results').hide();
			$('#asea_advanced_search_terms').trigger( 'focus' );
		}
	});
	
	$(document).on('click', function(e) {
		if ( $('#asea_search_results').css('display') !== 'none' ) { 
			$('#asea_search_results').hide();
		}
	});
	
	$('.epkb-editor-preview #asea-sub-section-1-3').on( 'click', function() {
		$('.asea-search-filter-container').removeClass('asea-search-filter-fadeIn-animation');
		$('.asea-search-filter-container').addClass('asea-search-filter-fadeOut-animation');
		$('.asea-search-filter-container').hide();
		$('#asea_search_results').css('display', 'none');
	});
	
	
	$('#asea_search_form').on('click', function(e){
		if ( $('.epkb-editor-preview').length == 0 ) {	
			e.stopPropagation();
		}
	});
	
	$('.asea-search-filter-icon-container').on('click', function ( e ) {

		e.stopPropagation();
		
		// if it is editor and zone is not active  hide and don't show search results 
		if ( $('.epkb-editor-preview').length && ! $('#asea-doc-search-box-container').hasClass('epkb-editor-zone--active') ) {
			$('.asea-search-filter-container').removeClass('asea-search-filter-fadeIn-animation');
			$('.asea-search-filter-container').addClass('asea-search-filter-fadeOut-animation');
			$('.asea-search-filter-container').hide();
			$('#asea_search_results').css('display', 'none');
			return;
		}
		
		//If this has the class it's already open
		if ($('.asea-search-filter-container').hasClass('asea-search-filter-fadeIn-animation')) {

			//Fade out the drop down
			$('.asea-search-filter-container').removeClass('asea-search-filter-fadeIn-animation');
			$('.asea-search-filter-container').addClass('asea-search-filter-fadeOut-animation');
			$('.asea-search-filter-container').hide();

		} else {
			//Hide Search results
			$('#asea_search_results').css('display', 'none');

			//Fade in the drop down
			$('.asea-search-filter-container').removeClass('asea-search-filter-fadeOut-animation');
			$('.asea-search-filter-container').addClass('asea-search-filter-fadeIn-animation');
			$('.asea-search-filter-container').show();
		}
	});

	function asea_live_search(request, response) {

		//Hide Filter DropDown
		if ($('.asea-search-filter-container').hasClass('asea-search-filter-fadeIn-animation')) {

			//Fade out the drop down
			$('.asea-search-filter-container').removeClass('asea-search-filter-fadeIn-animation');
			$('.asea-search-filter-container').addClass('asea-search-filter-fadeOut-animation');
		}

		if (!request.term) {
			return;
		}

		var categories = [];
		$('.asea-search-filter-container .asea-filter-option-input:checked').each(function () {
			categories.push($(this).val());
		});
		var postData = {
			action: 'asea-advanced-search-kb',
			asea_kb_id: $('#asea_kb_id').val(),
			search_categories: categories.join(',') || [],
			search_words: request.term,
			is_kb_main_page: $('.eckb_search_on_main_page').length
		};

		if( $('#asea_seq_id').length ){
			postData['asea_seq_id'] = $('#asea_seq_id').val();
		}
		var msg = '';

		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: postData,
			url: asea_vars.ajaxurl,
			beforeSend: function (xhr) {
				//Loading Spinner
				$('.loading-spinner').css('display', 'block');
				$('#asea-ajax-in-progress').show();
			}

		}).done(function (response) {
			response = (response ? response : '');

			//Hide Spinner
			$('.loading-spinner').css('display', 'none');

			if (response.error || response.status !== 'success') {
				//noinspection JSUnresolvedVariable
				msg = asea_vars.msg_try_again;
			} else {
				msg = response.search_result;
			}

		}).fail(function (response, textStatus, error) {
			//noinspection JSUnresolvedVariable
			msg = asea_vars.msg_try_again + '. [' + (error ? error : asea_vars.unknown_error) + ']';

		}).always(function () {
			$('#asea-ajax-in-progress').hide();

			if (msg) {
				$('#asea_search_results').css('display', 'block');
				$('#asea_search_results').html(msg);
			}
		});
	}

	// handle search results page
	/*	$('#asea_advanced_search_terms').keypress(function(event) {
			event.preventDefault();
			var request  = { term: $("#asea_advanced_search_terms").val() };
			asea_live_search( request, '' );
		}); */

	// since we have live search, don't handle enter
	$('#asea_advanced_search_terms').on( 'keypress', function (event) {
		if (event.keyCode == 13) {  // handling enter will cause search to be invoked by this and by echo_autocomplete
			event.preventDefault();
			//var request  = { term: $("#asea_advanced_search_terms").val() };
			//asea_live_search( request, '' );
		}
	});

	// cleanup search if search keywords deleted
	$("#asea_advanced_search_terms").on( 'keyup', function () {
		if (!this.value) {
			$('#asea_search_results').css('display', 'none');
		}
	});

	//Show Hide Search Box for Advanced Search box.
	$('.asea-search-toggle').on('click', function () {
		$(this).parent().find('#asea-doc-search-container').slideToggle();
	});

	// open/hide subcategories in the filter 
	$('body').on('change', '.asea-search-filter-container.sub input[type=checkbox]', function(){
		// should work only for top categories 
		if ( $(this).closest('ul.children').length ) {
			return;
		}
		
		if ( $(this).prop('checked') ) {
			$(this).closest('li').find('ul').show();
			$(this).closest('li').find('input[type=checkbox]').prop('checked', true);
		} else {
			$(this).closest('li').find('ul').hide();
			$(this).closest('li').find('input[type=checkbox]').prop('checked', false); // disable hidden checkboxes
		}
	});

	// Category Filter - uncheck parent checkbox if all sub checkbox unchecked
	$('body').on('change', '.asea-search-filter-container.sub ul.children input[type=checkbox]', function(){
		let checked_parent = false;
		let parent_ul = $(this).closest('ul.children');
		parent_ul.find('input[type=checkbox]').each(function( index ) {
			if ( $(this).prop('checked') ) {
				checked_parent = true;
			}
		});
		if ( ! checked_parent ) {
			parent_ul.hide();
			parent_ul.prev('.asea-filter-option').find('input[type=checkbox]').prop('checked', false);
		}

	});

	// Trigger search after changing filter category only if user returned to the main search input or closed the filter drop down
	$( '.asea-search-box' ).on( 'click', function() {
		if ( $( '.asea-search-filter-container' ).hasClass( 'asea-search-filter-fadeIn-animation' ) ) {
			let request  = { term: $( '#asea_advanced_search_terms' ).val() };
			asea_live_search( request, '' );
		}
	} );
});
