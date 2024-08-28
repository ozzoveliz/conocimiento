jQuery(document).ready(function($) {

    /********************************************************************
     *                      Search
     ********************************************************************/

    // handle KB search form
    $( 'body' ).on( 'submit', '.widg-search-form', function( e ) {
        e.preventDefault();  // do not submit the form

        var this_form = $( this );

        if ( this_form.find('.widg-search-terms').val() === '' ) {
            return;
        }

        var postData = {
            action: 'widg-search-kb',
            widg_kb_id: this_form.find('#widg_kb_id').val(),
            search_words: this_form.find('.widg-search-terms').val(),
            search_results_limit: this_form.find('#search_results_limit').val()
        };

        var msg = '';

        $.ajax({
            type: 'GET',
            dataType: 'json',
            data: postData,
            url: widg_vars.ajaxurl,
            beforeSend: function (xhr)
            {
                //Loading Spinner
                this_form.find( '.widg-loading-spinner').css( 'display','block');
                this_form.find('#widg-ajax-in-progress').show();
            }

        }).done(function (response) {
            response = ( response ? response : '' );

            //Hide Spinner
            this_form.find( '.widg-loading-spinner').css( 'display','none');

            if ( response.error || response.status !== 'success') {
                //noinspection JSUnresolvedVariable
                msg = widg_vars.msg_try_again;
            } else {
                msg = response.search_result;
            }

        }).fail(function (response, textStatus, error) {
            //noinspection JSUnresolvedVariable
            msg = widg_vars.msg_try_again + '. [' + ( error ? error : widg_vars.unknown_error ) + ']';

        }).always(function () {
            this_form.find('#widg-ajax-in-progress').hide();

            if ( msg ) {
                this_form.find( '.widg-search-results' ).css( 'display','block' );
                this_form.find( '.widg-search-results' ).html( msg );

            }

        });
    });

    $(".widg-search-terms").on( 'keyup', function() {

        if (!this.value) {
            $('.widg-search-results').css( 'display','none' );
        }

    });

    // Hide Search results when click triggered.
    $( 'body' ).on( 'click', function() {
        $( '.widg-search-results' ).hide();
    });
});
