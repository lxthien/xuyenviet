webpackJsonp([2],{

/***/ "./web/assets/js/front/app.js":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./web/assets/js/front/pages/news.js");

/***/ }),

/***/ "./web/assets/js/front/pages/news.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {function intHandleFormComment() {
    var $formComment = $('#form-comment');

    $formComment.on('click', '#form_send', function (e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: $formComment.attr('action'),
            data: $formComment.serialize(),
            success: function success(data) {
                var response = JSON.parse(data);
                if (response.status == 'success') {
                    $('p#comment-response').html(response.message);
                } else {
                    alert(data);
                }
            }
        });
    });
}

module.exports = function () {
    intHandleFormComment();
};
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__("./node_modules/jquery/dist/jquery.js")))

/***/ })

},["./web/assets/js/front/app.js"]);