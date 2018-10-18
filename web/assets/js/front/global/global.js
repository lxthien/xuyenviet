'use strict';

require('bxslider/dist/jquery.bxslider');

function initProtectedContent() {
    $('body').bind('cut copy paste', function (e) {
        e.preventDefault();
    });
}

function initGoToTop() {
    var $goToTop = $('.go-to-top');

    $goToTop.click(function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });
}

function initPartnerSlider() {
    $('.bxslider').show().bxSlider({
        auto: true,
        autoControls: false,
        stopAutoOnClick: true,
        pager: true,
        controls: false,
        minSlides: 3,
        maxSlides: 6,
        moveSlides: 1,
        slideWidth: 200,
        onSliderLoad: function() {
            $('.bxslider-wrapper').css("visibility", "visible");
        }
    });
}

exports.init = function () {
    initPartnerSlider();
    initProtectedContent();
    initGoToTop();
};