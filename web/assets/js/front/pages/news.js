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
                if (response.status === 'success') {
                    $('p#comment-response').html(response.message);

                    // Clear form comment
                    $formComment[0].reset();
                } else {
                    alert(data);
                }
            }
        });
    })
}

function intHandleFormReplyComment() {
    var $commentReply = $('#comment-reply');
    var $formComment = $('#form-comment');

    $commentReply.click(function(e) {
        e.preventDefault();

        var postID = $(this).data('postId');

        if (postID) {
            $formComment.find('input#form_comment_id').val(postID);

            $('html, body').animate({
                scrollTop: $formComment.offset().top
            }, 1000);
        }
    });
}

exports.init = function () {
    intHandleFormComment();
    intHandleFormReplyComment();
};