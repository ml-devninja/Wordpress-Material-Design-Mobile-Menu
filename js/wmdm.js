$(document).ready(function () {
    var timer = 300;
    $('.wmdm-opener').click(function(){
        $('.mobile-menu-toggle').velocity({left:0}, timer);
    });

    $('.close-menu, .menu-item ').click(function () {
        $('.mobile-menu-toggle').velocity({left:'-100%'}, timer);
    });

    $('.q-action').eq(1).addClass('middle');
});
