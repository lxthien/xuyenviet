'use strict';

require('bxslider/dist/jquery.bxslider');

function initSearchBox() {
    var $formSearch = $('#form-search');
    var $searchField = $('.search-field');

    $searchField.keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();

            if ($searchField.val() === '') {
                $searchField.focus();
            } else {
                $formSearch.submit();
            }
        }
    })
}

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
    initSearchBox();
    initPartnerSlider();
    initProtectedContent();
    initGoToTop();
};