'use strict';

function intHandleFormComment() {
    var $formComment = $('#form-comment');

    $formComment.on('click', '#form_send', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: $formComment.attr('action'),
            data: $formComment.serialize(),
            success: function(data) {
                var response = JSON.parse(data);
                if (response.status == 'success') {
                    $('p#comment-response').html(response.message);
                } else {
                    alert(data);
                }
            }
        });
    })
}

function intHandleFormReplyComment() {
    var $commentReply = $('#comment-reply');

    $commentReply.click(function(e) {
        e.preventDefault();

        var postID = $(this).data('postId');
        if (postID.length) {
            alert(postID);
        }
    });
}

exports.init = function () {
    intHandleFormComment();
    intHandleFormReplyComment();
};