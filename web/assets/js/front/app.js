'use strict';

var $ = require('jquery');

var news = require('./pages/news');
var news = require('./global/global');

var app = {
    init: function () {
        news.init();
    }
};

// initialize app
$(document).ready(function () {
    app.init();
});