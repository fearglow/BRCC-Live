(function ($) {
    var old_order_id = !1;

    // Add to cart
    $(document).on('click', '.btn-st-add-cart', function () {
        var me = $(this);
        var sform = me.closest('form');
        var holder = $('.message_box');
        var dataobj = sform.serialize();
        dataobj += '&action=st_add_to_cart';

        me.addClass('loading');
        holder.html('');

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: st_params.ajax_url,
            data: dataobj,
            success: function (data) {
                me.removeClass('loading');
                if (data.message) {
                    setMessage(holder, data.message, 'danger');
                }
                if (data.status) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            },
            error: function () {
                me.removeClass('loading');
            }
        });
    });

    // Open cart modal
    $('.btn-st-show-cart-modal').on('click', function () {
        var me = $(this);
        $.magnificPopup.open({
            items: { type: 'inline', src: me.data('target') },
            close: function () {
                old_order_id = !1;
            }
        });
        get_cart_detail(me.data('target'));
    });

    // Fetch cart details into modal
    function get_cart_detail(dom) {
        var dom_div = dom + " .booking-item-payment";
        var me = $(dom_div);
        me.find('.overlay-form').show();

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: st_params.ajax_url,
            data: { action: 'modal_get_cart_detail' },
            success: function (result) {
                me.html(result);
                me.find('.overlay-form').hide();
            },
            error: function () {
                me.find('.overlay-form').hide();
            }
        });
    }

    // Submit from checkout (modal)
    $(document).on('click', '.booking_modal_form .btn-st-checkout-submit', function () {
        // If already disabled, do nothing
        if ($(this).hasClass('disabled')) {
            return false;
        }
        // Disable button visually
        $(this).addClass('disabled')
               .css({ 'pointer-events': 'none', 'opacity': '0.6' });

        var form = $(this).closest('form');
        form.trigger('st_before_checkout_modal');

        var payment = $('input[name="st_payment_gateway"]:checked', form).val();
        var wait_validate = $('input[name="wait_validate_' + payment + '"]', form).val();

        if (wait_validate === 'wait') {
            form.trigger('st_wait_checkout_modal');
            return false;
        }
        form.STSendModalBookingAjax();
    });

    // Smooth scroll helper
    function do_scrollTo(el) {
        if (el.length) {
            var top = el.offset().top;
            if ($('#wpadminbar').length && $('#wpadminbar').css('position') === 'fixed') {
                top -= 32;
            }
            top -= 300;
            $('html,body').animate({ 'scrollTop': top }, 500);
        }
    }

    // Show messages in container
    function setMessage(holder, message, type) {
        if (typeof type === 'undefined') {
            type = 'infomation';
        }
        var html = '<div class="alert alert-' + type + '">' + message + '</div>';
        if (!holder.length) return;
        holder.html(html);

        if (holder.offset().top > $(window).height()) {
            do_scrollTo(holder);
        }
    }

    // Validate booking form if required
    function checkRequiredBooking(searchbox) {
        var searchform = $('.booking-item-dates-change');
        if (typeof searchbox !== "undefined") {
            var data = searchbox.find('input,select,textarea').serializeArray();
        }
        var dataobj = {};
        for (var i = 0; i < data.length; ++i) {
            dataobj[data[i].name] = data[i].value;
        }
        var holder = $('.search_room_alert');
        holder.html('');

        if (dataobj.room_num_search === "1") {
            if (
                dataobj.adult_number === "" ||
                dataobj.child_number === "" ||
                typeof dataobj.adult_number === 'undefined' ||
                typeof dataobj.child_number === 'undefined'
            ) {
                setMessage(holder, st_hotel_localize.booking_required_adult_children, 'danger');
                return !1;
            }
        }
        if (dataobj.check_in === "" || dataobj.check_out === "") {
            if (dataobj.check_in === "") {
                searchform.find('[name=start]').addClass('error');
            }
            if (dataobj.check_out === "") {
                searchform.find('[name=end]').addClass('error');
            }
            setMessage(holder, st_hotel_localize.is_not_select_date, 'danger');
            return !1;
        }
        return !0;
    }

    // Main booking function (modal)
    $.fn.STSendModalBookingAjax = function () {
        this.each(function () {
            var me = $(this);
            var button = $('.btn-st-checkout-submit', this);
            var data = me.serializeArray();
            data.push({ name: 'action', value: 'booking_form_direct_submit' });

            me.find('.form-control').removeClass('error');
            me.find('.form_alert').addClass('hidden');

            var dataobj = {};
            var form_validate = !0;
            for (var i = 0; i < data.length; ++i) {
                dataobj[data[i].name] = data[i].value;
            }

            // Client-side validation
            $('input.required,select.required,textarea.required', me).removeClass('error');
            $('input.required,select.required,textarea.required', me).each(function () {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    form_validate = !1;
                }
            });

            // If validation fails, show an error & re-enable right away
            if (form_validate === !1) {
                me.find('.form_alert')
                  .addClass('alert-danger')
                  .removeClass('hidden')
                  .html(st_checkout_text.validate_form);

                button.removeClass('loading disabled')
                      .css({ 'pointer-events': '', 'opacity': '' });
                return !1;
            }

            // Check terms
            if (!dataobj.term_condition) {
                me.find('.form_alert')
                  .addClass('alert-danger')
                  .removeClass('hidden')
                  .html(st_checkout_text.error_accept_term);

                button.removeClass('loading disabled')
                      .css({ 'pointer-events': '', 'opacity': '' });
                return !1;
            }

            dataobj.order_id = old_order_id;
            button.addClass('loading');

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: st_params.ajax_url,
                data: dataobj,
                success: function (data) {
                    // Always remove the "loading" class (spinner)
                    button.removeClass('loading');

                    // Check if there's a redirect (successful submission)
                    if (data.redirect || data.redirect_form) {
                        // Keep the button DISABLED on success
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                        if (data.redirect_form) {
                            $('body').append(data.redirect_form);
                        }
                    } else {
                        // No redirect => treat as an error or partial success => re-enable
                        button.removeClass('disabled')
                              .css({ 'pointer-events': '', 'opacity': '' });
                    }

                    // Show any message
                    if (data.message) {
                        me.find('.form_alert')
                          .addClass('alert-danger')
                          .removeClass('hidden')
                          .html(data.message);
                    }

                    // If an order ID is returned, update old_order_id
                    if (typeof data.order_id !== 'undefined' && data.order_id) {
                        old_order_id = data.order_id;
                    }

                    if (data.new_nonce) {
                        // handle new_nonce if needed
                    }

                    get_new_captcha(me);
                },
                error: function () {
                    // AJAX error => re-enable button
                    button.removeClass('loading disabled')
                          .css({ 'pointer-events': '', 'opacity': '' });

                    alert('Ajax Fail');
                    get_new_captcha(me);
                }
            });
        });
    };

    // Fallback submit (unused if STSendModalBookingAjax is used, but left for reference)
    function submit_form(me, clicked) {
        var button = clicked;
        var data = me.serializeArray();
        data.push({ name: 'action', value: 'booking_form_direct_submit' });

        me.find('.form-control').removeClass('error');
        me.find('.form_alert').addClass('hidden');

        var dataobj = {};
        var form_validate = !0;
        for (var i = 0; i < data.length; ++i) {
            dataobj[data[i].name] = data[i].value;
        }

        // Check required fields
        $('input.required,select.required,textarea.required', me).removeClass('error');
        $('input.required,select.required,textarea.required', me).each(function () {
            if (!$(this).val()) {
                $(this).addClass('error');
                form_validate = !1;
            }
        });

        if (form_validate === !1) {
            me.find('.form_alert')
              .addClass('alert-danger')
              .removeClass('hidden')
              .html(st_checkout_text.validate_form);

            button.removeClass('loading disabled')
                  .css({ 'pointer-events': '', 'opacity': '' });
            return !1;
        }

        if (!dataobj.term_condition) {
            me.find('.form_alert')
              .addClass('alert-danger')
              .removeClass('hidden')
              .html(st_checkout_text.error_accept_term);

            button.removeClass('loading disabled')
                  .css({ 'pointer-events': '', 'opacity': '' });
            return !1;
        }

        dataobj.order_id = old_order_id;
        button.addClass('loading');

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: st_params.ajax_url,
            data: dataobj,
            success: function (data) {
                button.removeClass('loading');

                if (data.redirect || data.redirect_form) {
                    // Keep disabled on success
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                    if (data.redirect_form) {
                        $('body').append(data.redirect_form);
                    }
                } else {
                    // Re-enable if no redirect => likely an error
                    button.removeClass('disabled')
                          .css({ 'pointer-events': '', 'opacity': '' });
                }

                if (data.message) {
                    me.find('.form_alert')
                      .addClass('alert-danger')
                      .removeClass('hidden')
                      .html(data.message);
                }
                if (typeof data.order_id !== 'undefined' && data.order_id) {
                    old_order_id = data.order_id;
                }
                if (data.new_nonce) {
                    // handle if needed
                }
                get_new_captcha(me);
            },
            error: function () {
                alert('Ajax Fail');
                button.removeClass('loading disabled')
                      .css({ 'pointer-events': '', 'opacity': '' });
                get_new_captcha(me);
            }
        });
    }

    // Refresh captcha if needed
    function get_new_captcha(me) {
        var captcha_box = me.find('.captcha_box');
        var url = captcha_box.find('.captcha_img').attr('src');
        captcha_box.find('.captcha_img').attr('src', url);
    }

    // Payment radio toggles
    $('.payment-item-radio').on('ifChecked', function () {
        var parent = $(this).closest('li.payment-gateway');
        var id = parent.data('gateway');
        parent.addClass('active').siblings().removeClass('active');
        $('.st-payment-tab-content .st-tab-content[data-id="' + id + '"]').siblings().fadeOut('fast');
        $('.st-payment-tab-content .st-tab-content[data-id="' + id + '"]').fadeIn('fast');
    });
})(jQuery);
