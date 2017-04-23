(function( $ ) {

    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {


    $('.wmdm-opener, .icon-close, .menu-item ').click(function () {
        $('.mobile-menu-toggle, .header-mobile').toggleClass('open');
    });

    $('.q-action').eq(1).addClass('middle');

    $('.wmdm-search > span').on('click', function () {
        $('.wmdm-search').addClass('search');
    })


        $('.q-bar > .unfolded').on("click", function () {
            $(this).addClass('open');
            $(this).parent().addClass('open')
        })
        $('.q-bar-back').on('click', function () {
            $('.q-bar, .unfolded').removeClass('open');
        })

        $('.wmdm-search input').focus( function () {
            $(this).closest('form').addClass('focused');
        }).focusout( function () {
            $(this).closest('form').removeClass('focused');
        })


        /**
         * GESTURES
         */
        $( "body" ).on( "swiperight", function () {
            $('.mobile-menu-toggle, .header-mobile').addClass('open');
        } );
        $( "body" ).on( "swipeleft", function () {
            $('.mobile-menu-toggle, .header-mobile').removeClass('open');
        } );

    });

})( jQuery );
