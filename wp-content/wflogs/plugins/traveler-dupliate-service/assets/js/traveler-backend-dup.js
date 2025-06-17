(function($){
    'use strict';
   $(document).ready(function(){
       $('.title.column-title').each(function(){
            let parent = $(this);
            $('.stt-duplicate',parent).on('click',function(){
                    $('.duplicate-popup',parent).show();
            });
            $('.duplicate-popup .close-filter').on('click',function(){
                $('.duplicate-popup',parent).hide();
            });
       });
       
       $('.duplicate-popup').each(function(){
            var p = $(this);
            
            $('#stt_duplicate_room',p).on('click',function(){
                var checkbox_room = $('#stt_duplicate_room',p).is(':checked') ?1 :0 ;
                if(checkbox_room == '1'){
                   $('.duplicate-room-availability',p).css('display','flex')
                }else{
                    $('.duplicate-room-availability',p).hide()  
                }
            });
            
            $('.stt-dup-button',p).on('click',function(ev){
                ev.preventDefault();
                let t = $(this);
                var data = {
                    'stt_duplicate_number' : $('.stt-duplicate-number',p).val(),
                    'stt_check' : $('#stt_duplicate_availability',p).is(':checked') ? 1 :0,
                    'stt_check_room' : $('#stt_duplicate_room',p).is(':checked') ? 1 :0,
                    'stt_check_room_availability' : $('#stt_duplicate_room_availability',p).is(':checked') ? 1 :0,
                    'post_id': t.data('id'),
                    'action': t.data('action')
                };
                t.html(t.data('process'));
                $.post(st_params.ajax_url,data,function(respon){
                    if(typeof respon === 'object' ){
                        if(respon.status == 1){
                            t.html(t.data('finish'));
                            $('.duplicate-message',p).html(respon.message);
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        }else{
                            t.html(t.data('finish'));
                            $('.duplicate-message',p).html(respon.message);
                        }
                    }
                },'json');
            });
       });
   });
})(jQuery);
