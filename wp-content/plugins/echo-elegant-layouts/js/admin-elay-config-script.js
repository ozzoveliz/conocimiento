jQuery(document).ready(function($) {

    /********************************************************************************************
     *
     *                STYLE SIDEBAR
     *
     /********************************************************************************************/

     // highlight GRID category that user clicked on
    $( '[id$=pkb-main-page-content]' ).on( 'click', '.elay-top-category-box', function(){

        if ( ! $('[id$=pkb-config-styles-sidebar]').is(':visible') ) {
            return;
        }

        // clear Grid Category selection
        $( '.elay-top-category-box' ).removeClass('elay-grid-category-selection-color');

        // select this element
        $(this).addClass('elay-grid-category-selection-color');

        $('#elay-config-category-name').text( $(this).find('.elay-grid-category-name').text() );
    });

    // user clicks back
    $( '#ekb-admin-page-wrap' ).find( '[class$=pkb-menu-back]' ).on( 'click', function(e){
        // clear Grid Category selection
        $( '.elay-top-category-box' ).removeClass('elay-grid-category-selection-color');
    
    });


    /********************************************************************************************
     *
     *                CATEGORY SECTIONS
     *
     ********************************************************************************************/

    {
        var sidebar = $('[id$=pkb-main-page-content],[id$=pkb-article-page-content]');

        /**
         * 1. ICON TOGGLE for Top / Sub Category - toggle between open icon and close icon
         */
		sidebar.on('click', '.sidebar-sections .elay-category-level-1, .sidebar-sections .elay-category-level-2-3', function () {
            var icon = $(this).find('i').eq(0);
            if ( icon.length > 0 ) {
                elay_toggle_category_icons(icon, icon.attr('class').match(/\ep_font_icon_\S+/g)[0]);
            }
        });

        function elay_toggle_category_icons( icon, icon_name ) {

            var icons_closed = [ 'ep_font_icon_plus', 'ep_font_icon_plus_box', 'ep_font_icon_right_arrow', 'ep_font_icon_arrow_carrot_right', 'ep_font_icon_arrow_carrot_right_circle', 'ep_font_icon_folder_add' ];
            var icons_opened = [ 'ep_font_icon_minus', 'ep_font_icon_minus_box', 'ep_font_icon_down_arrow', 'ep_font_icon_arrow_carrot_down', 'ep_font_icon_arrow_carrot_down_circle', 'ep_font_icon_folder_open' ];

            var index_closed = icons_closed.indexOf( icon_name );
            var index_opened = icons_opened.indexOf( icon_name );

            if ( index_closed >= 0 ) {
                icon.removeClass( icons_closed[index_closed] );
                icon.addClass( icons_opened[index_closed] );
            } else if ( index_opened >= 0 ) {
                icon.removeClass( icons_opened[index_opened] );
                icon.addClass( icons_closed[index_opened] );
            }
        }

        /**
         *  2. SHOW ITEMS in TOP/SUB-CATEGORY
         */
        // TOP-CATEGORIES: show or hide article in sliding motion
		$('body').on('click', '.sidebar-sections .elay-top-class-collapse-on .elay-category-level-1', function () {

			$( this).parent().next().slideToggle();
        });
        // SUB-CATEGOREIS: show or hide article in sliding motion
		$('body').on('click', '.sidebar-sections .elay-category-level-2-3, .elay-sidebar__cat__top-cat .elay-category-level-2-3', function () {

			// show lower level of categories and show articles in this category
			if ( $( this ).next().css('display') == 'none' ) { // prev state 
				$( this ).parent().children('ul').slideDown();
			} else {
				$( this ).parent().children('ul').slideUp();
			}

        });

        /**
         * 3. SHOW ALL articles functionality
         *
         * When user clicks on the "Show all articles" it will toggle the "hide" class on all hidden articles
         */
		sidebar.on('click', '.elay-show-all-articles', function () {

            $(this).toggleClass('active');
            var parent = $(this).parent('ul');
            var article = parent.find('li');

            //If this has class "active" then change the text to Hide extra articles
            if ($(this).hasClass('active')) {

                //If Active
                $(this).find('.elay-show-text').addClass('elay-hide-elem');
                $(this).find('.elay-hide-text').removeClass('elay-hide-elem');

            } else {
                //If not Active
                $(this).find('.elay-show-text').removeClass('elay-hide-elem');
                $(this).find('.elay-hide-text').addClass('elay-hide-elem');
            }

            $(article).each(function () {

                //If has class "hide" remove it and replace it with class "Visible"
                if ($(this).hasClass('elay-hide-elem')) {
                    $(this).removeClass('elay-hide-elem');
                    $(this).addClass('visible');
                } else if ($(this).hasClass('visible')) {
                    $(this).removeClass('visible');
                    $(this).addClass('elay-hide-elem');
                }
            });
        });
    }

});