'use strict';

function protectedContent() {
    $('body').bind('cut copy paste', function (e) {
        e.preventDefault();
    });
}

function goToTop() {
    var $goToTop = $('.go-to-top');

    $goToTop.click(function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });
}

exports.init = function () {
    protectedContent();
    goToTop();
};