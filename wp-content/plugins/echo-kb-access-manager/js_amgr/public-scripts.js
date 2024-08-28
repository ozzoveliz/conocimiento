jQuery(document).ready(function($) {

    var amgr_redirect_url = $('#amgr-redirect-to-page').data('amgr-redirect-url');
    if (amgr_redirect_url)
    {
        setTimeout(function()
        {
            window.location.href = amgr_redirect_url;
        }, 2000);
    }
});