jQuery(document).ready(function($) {
	let current_vote = -1;

	// like = 5 (like) or 0 (dislike)
	// send ajax to change article rating id db
	function update_post_rating(rating_value, not_silent = true) {
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return false;
		}

		current_vote = rating_value;
		$('.eprf-article-feedback__title').show();
		$('.eprf-article-feedback__required-title').hide();

		// check if the form required for this vote
		let stop_update = false;

		if ( $('.eprf-like-dislike-module').hasClass('eprf-like-dislike-module-required-negative-five') && rating_value < 5 ) {
			stop_update = true;
		}

		if ( $('.eprf-stars-module').hasClass('eprf-stars-module-required-negative-four') && rating_value < 4 ) {
			stop_update = true;
		}

		if ( $('.eprf-stars-module').hasClass('eprf-stars-module-required-negative-five') && rating_value < 5 ) {
			stop_update = true;
		}

		if ( stop_update && not_silent ) {
			$('.eprf-article-feedback__title').hide();
			$('.eprf-article-feedback__required-title').show();
			$(document.body).trigger('open_article_feedback_form');
			return false;
		}

		let article_id = $( '#eckb-article-content' ).data( 'article-id' );
		if ( typeof article_id === 'undefined' ) {
			return false;
		}

		var postData = {
            action: 'eprf-update-rating',
            article_id: article_id,
            rating_value: rating_value,
        };

		postData[ '_wpnonce_eprf_ajax_action' ] = eprf_vars.nonce;

		// loader
		 $.ajax({
            type: 'GET',
            dataType: 'json',
            data: postData,
            url: eprf_vars.ajaxurl,
            beforeSend: function (xhr)
            {
				//$('#eprf-current-rating').html($('#eprf-current-rating').data('recording your vote'));
            }

        }).done(function (response) {

        	if ( not_silent ) {

				$('#eprf-current-rating').html(response.message);

				// open feedback form if need
				if (rating_value > 0 && $('.eprf-article-feedback-container--trigger-user-votes').length) {
					$(document.body).trigger('open_article_feedback_form');
				}

				if (rating_value < 5 && $('.eprf-article-feedback-container--trigger-negative-five').length) {
					$(document.body).trigger('open_article_feedback_form');
				}

				if (rating_value < 4 && $('.eprf-article-feedback-container--trigger-negative-four').length) {
					$(document.body).trigger('open_article_feedback_form');
				}

				if (rating_value < 4 && $('.eprf-article-feedback-container--trigger-dislike').length) {
					$(document.body).trigger('open_article_feedback_form');
				}
			}

			if ($('.eprf-stars-container').length) {
				$('.eprf-stars-container').data('average', rating_value).trigger('update_view');
				$('.eprf-stars-container').addClass('disabled');
			}

			// update ratings
			if (response.rating !== undefined) {
				if ($('.eprf-like-dislike-module__buttons').length && typeof response.rating.statistic != "undefined" && response.rating.statistic) {
					$('.eprf-like-count').text(response.rating.statistic.like);
					$('.eprf-dislike-count').text(response.rating.statistic.dislike);
				}

				if ( $('.eprf-stars-module__statistics').length && typeof response.statistics != "undefined" && response.rating.statistic ) {
					$('.eprf-stars-module__statistics').html( response.statistics );
				}

				if ( $('.eprf-article-meta__statistics').length && typeof response.statistics != "undefined" && response.rating.statistic ) {
					$('.eprf-article-meta__statistics').html( response.statistics );
				}
			}

			$('body').find('.eprf-stars-container.disabled').css({'cursor':'default'});
			$('.epkbfa-star').on( 'click', false);

			$('body').find('.eprf-like-dislike-module__buttons .eprf-rate-like, .eprf-like-dislike-module__buttons .eprf-rate-dislike').css({'cursor':'default'});
			$('body').find('.eprf-like-dislike-module__buttons .eprf-rate-like, .eprf-like-dislike-module__buttons .eprf-rate-dislike').on( 'click', false);
			$('.eprf-like-dislike-module').addClass('eprf-rating--blocked');

        }).fail(function (response, textStatus, error) {
            //noinspection JSUnresolvedVariable
            msg = eprf_vars.msg_try_again + '. [' + ( error ? error : eprf_vars.unknown_error ) + ']';
        });
	}

	// handlers for like/dislike mode
	$('.eprf-like-dislike-module:not(.eprf-rating--blocked) .eprf-rate-like').on( 'click', function(){
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return;
		}
		
		if (!$(this).closest('.eprf-like-dislike-module').hasClass('eprf-rating--blocked')) {
			update_post_rating(5);
		}
		return false;
	});

	$('.eprf-like-dislike-module:not(.eprf-rating--blocked) .eprf-rate-dislike').on( 'click', function(){
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return;
		}
		
		if (!$(this).closest('.eprf-like-dislike-module').hasClass('eprf-rating--blocked')) {
			update_post_rating(1);
		}
		return false;
	});

	function showStatistics() {
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return;
		}
		
		var stars = $('.eprf-stars-container');

		var stars_top; // relative position of the bottom-middle point of the stars block
		var stars_left; // relative position of the bottom-middle point of the stars block

		stars_top = stars.position().top + stars.height();
		stars_left = stars.position().left + stars.width()/2;

		var statistisc = $('.eprf-stars-module .eprf-stars-module__statistics');

		statistisc.css({
			'left' : (stars_left - statistisc.width()/2) + 'px',
			'top' : (stars_top + 5) + 'px'
		});
	}

	$(document.body).on('click', '.eprf-show-statistics-toggle', showStatistics);
	$(document.body).on('click', '.eprf-show-statistics-toggle', function(){
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return;
		}
		
		let element = $('.eprf-stars-module .eprf-stars-module__statistics');
		element.toggle();
		if ( $(element).is(":visible") ) {
			eprfHideOnClickOutside(element);
		}
	});

	// Toggle Meta Statistics for stars.
	$(document.body).on('click', '.eprf-article-meta__statistics-toggle', function(){
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return;
		}
		
		let element = $( this ).parent().find( '.eprf-article-meta__statistics' );
		element.toggle();
		if ( $(element).is(":visible") ) {
			eprfHideOnClickOutside(element);
		}
	});

	// open form trigger
	$(document.body).on('open_article_feedback_form', function(){

		if ($('.eprf-article-feedback-container--trigger-user-votes').length) {
			$('.eprf-article-feedback-container--trigger-user-votes').slideDown();
		} else if ($('.eprf-article-feedback-container--trigger-negative-four').length) {
			$('.eprf-article-feedback-container--trigger-negative-four').slideDown();
		} else if ($('.eprf-article-feedback-container--trigger-negative-five').length) {
			$('.eprf-article-feedback-container--trigger-negative-five').slideDown();
		} else if ($('.eprf-article-feedback-container--trigger-dislike').length) {
			$('.eprf-article-feedback-container--trigger-dislike').slideDown();
		} else if ($('.eprf-stars-module-required-negative-five, .eprf-stars-module-required-negative-four')) {
			$('#eprf-article-feedback-container').slideDown();
		} else {
			$('.eprf-form-row').slideDown();
		}

		$('#eprf-article-feedback-container button').addClass('openned');
		$('.eprf-open_feedback_form').hide();
	});

	// send feedback form
	$('.eprf-leave-feedback-form').on( 'submit', function(){
		
		if ( $('body').hasClass( 'epkb-editor-preview' ) ) {
			return false;
		}

		let article_id = $( '#eckb-article-content' ).data( 'article-id' );
		if ( typeof article_id === 'undefined' ) {
			return false;
		}
		
		var postData = {
			action: 'eprf-add-comment',
			article_id: article_id,
			current_vote: current_vote,
			name: '',
			email: '',
			comment: '',
			catch_details: ''
		};

		postData[ '_wpnonce_eprf_ajax_action' ] = eprf_vars.nonce;

		if($('#eprf-form-name').length) postData.name = $('#eprf-form-name').val();
		if($('#eprf-form-email').length) postData.email = $('#eprf-form-email').val();
		if($('#eprf-form-text').length) postData.comment = $('#eprf-form-text').val();
		if($('#eprf-form-details').length) postData.catch_details = $('#eprf-form-details').val();

		// loader
		 $.ajax({
            type: 'GET',
            dataType: 'json',
            data: postData,
            url: eprf_vars.ajaxurl,
            beforeSend: function (xhr)
            {
				$('#eprf-current-rating').html( '<div class="eprf-article-buttons__feedback-confirmation__loading"><div class="eprf-article-buttons__feedback-confirmation__loading__icon epkbfa epkbfa-spinner"></div> '+$('#eprf-current-rating').data('loading') + '</div>' );
            }

        }).done(function (response) {
			$('#eprf-current-rating').html(response.message);
			$('#eprf-article-feedback-container').slideUp();
			$('.eprf-open_feedback_form').hide();

			if ( response.status == 'success_1' ) {
				return;
			}

			if ( $('.eprf-stars-container').length && ! $('.eprf-stars-container').hasClass('disabled') && $('.eprf-stars-container').closest('.eprf-rating--blocked').length == 0 ) {

				update_post_rating($('.eprf-stars-container').data('average'), false);
			 }

			if ( $('.eprf-like-dislike-module').length && ! $('.eprf-like-dislike-module').hasClass('eprf-rating--blocked') ) {

				update_post_rating(1, false);
			}

        }).fail(function (response, textStatus, error) {
            //noinspection JSUnresolvedVariable
            msg = eprf_vars.msg_try_again + '. [' + ( error ? error : eprf_vars.unknown_error ) + ']';
        });
		return false;
	});

	// flex stars
	function redraw_stars($el, val) {
		$el.find('.eprf-stars__top-lay').hide();

		$el.find('.eprf-stars__inner-background .epkbfa').removeClass('epkbfa-star').removeClass('epkbfa-star-o').removeClass('epkbfa-star-half-o');

		$i = 0;
		$el.find('.eprf-stars__inner-background .epkbfa').each(function(){
			if ( val >= $i + 1 ) {
				$(this).addClass('epkbfa-star').removeClass('epkbfa-star-o').removeClass('epkbfa-star-half-o');
			} else if ( val >= $i + 0.5 ) {
				$(this).removeClass('epkbfa-star').removeClass('epkbfa-star-o').addClass('epkbfa-star-half-o');
			} else {
				$(this).removeClass('epkbfa-star').addClass('epkbfa-star-o').removeClass('epkbfa-star-half-o');
			}
			$(this).removeClass('epkbfa-star--selected');
			if ( val == $i + 1 ) {
				$(this).addClass('epkbfa-star--selected');
			}
			$i++;
		});
	}

	// detect a 'touch screen' device
	function isTouchDevice() {
		return ( ( 'ontouchstart' in window ) || ( navigator.maxTouchPoints > 0 ) || ( navigator.msMaxTouchPoints > 0 ) );
	}

	$(document.body).on('update_view mouseout', '.eprf-stars-container', function(e){
		if ( ! $(this).hasClass('disabled') && ! isTouchDevice() ) {
			let percentX = $(this).data('average');
			redraw_stars($(this), percentX);
		}
	});

	// redrawing stars by click rating buttons. For mobi only


	$(document.body).on('mousemove', '.eprf-stars-container', function(e){
		if ( !$(this).hasClass('disabled') && $(this).closest('.eprf-rating--blocked').length == 0 && ! isTouchDevice() ) {
			let percentX = eprfGetRatingValue(e, this);
			redraw_stars($(this), percentX);
		}
	});

	const eprfDecreaseButton = document.querySelector( '.eprf-mobile-rating-btn.eprf-decrease' );
	const eprfIncreaseButton = document.querySelector( '.eprf-mobile-rating-btn.eprf-increase' );
	const ratingValueSpan = document.querySelector( '.eprf-mobile-rating-value' );

	let mobileRatingValue;
	if ( ratingValueSpan ) {
		mobileRatingValue = parseFloat( ratingValueSpan.innerText );
	}

	function updateMobileRatingDisplay(){
		let $main_container = $( '.eprf-stars-container' );
		$main_container.data( 'average', mobileRatingValue ).trigger( 'update_view' );
		ratingValueSpan.innerText = mobileRatingValue.toFixed( 1 );
		redraw_stars( $main_container, mobileRatingValue );
		eprfDecreaseButton.disabled = mobileRatingValue <= 1;
		eprfIncreaseButton.disabled = mobileRatingValue >= 5;
	}
	if ( eprfDecreaseButton ) {
		eprfDecreaseButton.addEventListener( 'click', function() {
			if ( mobileRatingValue > 1 ) {
				mobileRatingValue -= 0.5;
				updateMobileRatingDisplay();
			}
		});
	}
	if ( eprfIncreaseButton ) {
		eprfIncreaseButton.addEventListener( 'click', function() {
			if ( mobileRatingValue < 5 ) {
				mobileRatingValue += 0.5;
				updateMobileRatingDisplay();
			}
		});
	}

	if ( eprfDecreaseButton && eprfIncreaseButton && ratingValueSpan && mobileRatingValue ) {
		updateMobileRatingDisplay();
	}

	$(document.body).on('click', '.eprf-stars-container', function(e){
		if ( !$(this).hasClass('disabled') && $(this).closest('.eprf-rating--blocked').length == 0 ) {
			if ( ! isTouchDevice() ) {
				let percentX = eprfGetRatingValue(e, this);
				$(this).data('average', percentX).trigger('update_view');
				redraw_stars($(this), percentX);
				update_post_rating(percentX);
			} else {
				// show rating adjustment buttons
				setTimeout( function() {
					let element = $( '.eprf-stars-module__mobile-rating' );
					if ( $( element ).is( ":visible" ) ) {
						$( element ).hide();
					} else {
						$( element ).show();
					}
				}, 10);
			}
		}
	});

	// submit mobile rating result. For mobile only
	$( document.body ).on( 'click', '.eprf-stars-module__mobile-rating .eprf-mobile-rating__submit', function( e ){
		update_post_rating( mobileRatingValue );
		$( '.eprf-stars-module__mobile-rating' ).hide();
	});


	if ($('.eprf-stars-container').length) {
		$('.eprf-stars-container').trigger('update_view');
	}

	// hide dialog/popup after user clicks outside of it
	function eprfHideOnClickOutside(selector) {
		const outsideClickListener = (event) => {
			$target = $(event.target);
			if (!$target.closest(selector).length && !$target.hasClass('eprf-show-statistics-toggle') && !$target.hasClass('eprf-article-meta__statistics-toggle') && $(selector).is(':visible')) {
				$(selector).hide();
				removeClickListener();
			}
		};

		const removeClickListener = () => {
			document.removeEventListener('click', outsideClickListener)
		};

		document.addEventListener('click', outsideClickListener)
	}

	// Get rating stars value
	function eprfGetRatingValue(event, element){
		let filled_width = event.pageX - $(element).offset().left;
		if ( $('[dir=rtl]').length ) {
			filled_width = $(element).width() - event.pageX + $(element).offset().left;
		}
		return Math.round( ( filled_width / $(element).width() ) * 10 ) / 2;
	}

	// hide form
	$(document.body).on('click', '.eprf-leave-feedback-form--close', function(e){

		// do nothing if we are showing form always
		if ( $('#eprf-article-feedback-container').hasClass('eprf-article-feedback-container--trigger-always') ) {
			return;
		}

		let $form_container = $(this).closest('#eprf-article-feedback-container');

		// hide
		$form_container.hide();

		// show button to open form if exists
		$('.eprf-open_feedback_form').show();

		// reset stars
		let $stars_container = $form_container.parent().find('.eprf-stars-container');

		if ( $stars_container.length == 0 ) {
			return;
		}

		$stars_container.data('average', $stars_container.data('initial_average') ).trigger('update_view');
	});

	// trigger 'Button "Open Feedback Form"'
	$(document.body).on('click', '.eprf-open_feedback_form', function(){
		$(document.body).trigger('open_article_feedback_form');
		return false;
	});
});
