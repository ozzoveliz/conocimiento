let epkb_el = wp.element.createElement;
var __ = wp.i18n.__;
var epkb_registerPlugin = wp.plugins.registerPlugin;
var epkb_PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;

function EPRFArticleRating() {

	let eprf_rating_html = document.getElementById("eprf_rating_html").innerHTML;
	if ( typeof eprf_rating_html == 'undefined' ) {
		// should never run, added just in case
		console.log( 'Missing EPRF rating data.' );
		return '';
	}
	
    return epkb_el(
	    epkb_PluginPostStatusInfo,
        {
            className: 'eprf-article-rating'
        },
	    epkb_el(
            'label',
            {},
			__('Rating', 'echo-article-rating-and-feedback' )
        ),
	    epkb_el(
			wp.element.RawHTML,
			{'className' : 'eprf-ratings-gutenberg-editor-container'},
		    eprf_rating_html
        ),
	    epkb_el(
			'button',
			{
				'className' : 'gut_stars_reset',
				'id' : 'resetArticleRating',
			},
		    epkb_el(
				'span',
				{
					'className' : 'epkbfa epkbfa-undo',
					'aria-hidden' : 'true'
				},
				''
			)
        )
    );
}

epkb_registerPlugin( 'eprf-article-rating', {
    render: EPRFArticleRating
} );