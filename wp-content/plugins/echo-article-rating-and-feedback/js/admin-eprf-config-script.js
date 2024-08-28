jQuery(document).ready(function($) {
	// hide/show options for each mode on article rating setting page
	function eprf_check_mode() {
		if ($('.rating-mode').length) {
			var mode = $('#rating_mode').val();

			if ('eprf-rating-mode-five-stars' == mode) {
				$('.likedislike-mode').hide();
				$('.stars-mode').show();
			}

			if ('eprf-rating-mode-like-dislike' == mode) {
				$('.likedislike-mode').show();
				$('.stars-mode').hide();
			}
		}
	}

	$('#eckb-mm-ap-links-ratingandfeedback-features-articlerating, #eckb-mm-ap-links-ratingandfeedback-features-articlefeedback').on( 'click', eprf_check_mode);
	$('#rating_mode').on( 'change', eprf_check_mode);

	$(document.body).on('mouseleave', '.eprf-stars-module', function(){
		$('.eprf-stars-module .eprf-stars-module__statistics').hide();
	});

	// open form trigger
	$(document.body).on('open_article_feedback_form', function(){
		$('.eprf-form-row').slideDown();
		$('#eprf-article-feedback-container button').addClass('openned');
	});
});

