jQuery("#menu li").hover(
    function () {
        jQuery(this).find('ul:first').css({
            visibility: "visible",
            display: "none"
        }).stop(true, true).fadeIn(100);
    },
    function () {
        jQuery(this).find('ul:first').css({
            visibility: "visible",
            display: "block"
        }).stop(true, true).fadeOut(100);
    }
);

jQuery("#shopping-cart").hover(
    function () {
        jQuery(this).find('ul:first').css({
            visibility: "visible",
            display: "none"
        }).stop(true, true).fadeIn(100);
    },
    function () {
        jQuery(this).find('ul:first').css({
            visibility: "visible",
            display: "block"
        }).stop(true, true).fadeOut(100);
    }
);
