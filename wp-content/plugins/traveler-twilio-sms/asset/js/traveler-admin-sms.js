(function($){
    'use strict';
    var body = $('body');
    var textareaWidth = $('.textarea-sms .textarea-sms-content').width();
    $('.sms-shortcode').css({
        left: textareaWidth + 40 + 'px'
    });
})(jQuery);
