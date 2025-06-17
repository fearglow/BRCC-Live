(function($){
    'use strict';
    $(document).ready(function(){
        $('.date-history-swapper').each(function(){
            var parent = $(this),
                check_in_input = $('.check-in-input', parent),
                check_out_input = $('.check-out-input', parent),
                check_in_out = $('.check-in-out', parent),
                check_in_render = $('.check-in-render', parent),
                check_out_render = $('.check-out-render', parent);
                var options = {
                    singleDatePicker: false,
                    sameDate: true,
                    sameDateMulti: true,
                    autoApply: true,
                    disabledPast: false,
                    dateFormat: 'DD/MM/YYYY',
                    customClass: 'stt-export-date',
                    widthSingle: 500,
                    onlyShowCurrentMonth: true,
                };
                check_in_out.daterangepicker(options,function (start, end, label) {
                    check_in_input.val(start.format('DD/MM/YYYY'));
                    check_out_input.val(end.format('DD/MM/YYYY'));
                    check_in_render.html(start.format('DD/MM/YY'));
                    check_out_render.html(end.format('DD/MM/YYYY'));
                        
                });
                $('.check-in-wrapper', parent).click(function(){
                    check_in_out.trigger('click');
                });
                
                  
        });
        $('.form-select-2','#stt-history-popup').select2({ dropdownCssClass : "stt-status-select" });  
    });

})(jQuery);