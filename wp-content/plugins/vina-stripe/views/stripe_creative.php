<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 12/16/2015
 * Time: 6:03 PM
 */
?>
<?php

wp_enqueue_script( 'st-stripe-js' );
    wp_enqueue_style('stripe-css');
?>
<div class="pm-info">
    <div class="row">
        <div class="col-sm-12">
            <div class="col-card-info st-stripe-card-info vina-stripe-card-form">
                <div id="vina_strip_card_form"></div>
            </div>

            <input type="hidden" id="wait_validate_vina_stripe" name="wait_validate_vina_stripe" value="wait">
            <input type="hidden" name="vina_stripe_payment_method_id" id="vina_stripe_payment_method_id" value="">
        </div>
    </div>
</div>

<script type="text/javascript">
        jQuery(document).ready(function ($) {
            'use strict';
            var stripePublishKey = st_vina_stripe_params.vina_stripe.publishKey;

            if(st_vina_stripe_params.vina_stripe.sanbox == 'sandbox'){
                stripePublishKey = st_vina_stripe_params.vina_stripe.testPublishKey
            }
            cardholderName = '<?php echo __('Custommer',ST_TEXTDOMAIN);?>';

            if($('#field-st_first_name').length){
                var cardholderName = $('#field-st_first_name').val();

            }
            if($('#field-st_last_name').length){
                var lastName = $('#field-st_last_name').val();
                cardholderName += ' ' + lastName;
            }
            if(stripePublishKey == ''){
                return false;
            }


            var stripe = Stripe(stripePublishKey);

            var elements = stripe.elements();
			// Custom styling can be passed to options when creating an Element.
			// (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
                base: {
                    color: '#32325d',
                    lineHeight: '18px',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            // Create an instance of the card Element.
            var cardElement = elements.create('card', {style: style});
            var st_strip_alert = $('.vina_st_strip_alert');
            var wait_validate_st_stripe = $('input[name=wait_validate_st_stripe]');

            st_strip_alert.hide();

            // Add an instance of the card Element into the `card-element` <div>.
            cardElement.mount('#vina_strip_card_form');

            // Handle real-time validation errors from the card Element.
            cardElement.addEventListener('change', function(event) {
                if (event.error) {
                    st_strip_alert.html(event.error.message).show();
                } else {
                    st_strip_alert.hide();
                }
            });
            var vina_stripe_payment_method_id  =  $('#vina_stripe_payment_method_id');
            var wait_validate_vina_stripe  =  $('#wait_validate_vina_stripe');
            /*Cusstom*/
            var vina_tokenRequest = function (type) {
                stripe.createPaymentMethod('card', cardElement, {
                    billing_details: {
                        name: cardholderName
                    }
                }).then(function(result) {
                    console.log(result);
                    if (result.error) {
                        alert(result.error.message);
                    } else {
                        wait_validate_vina_stripe.val('run');
                        vina_stripe_payment_method_id.val(result.paymentMethod.id);
                        switch (type){
                            case 'modal':
                                $('.booking_modal_form').STSendModalBookingAjax();
                                break;
                            case 'form':
                                $('#cc-form').STSendAjax();
                                break;
                            case 'package':
                                var myForm = document.getElementById('mpk-form');
                                myForm.vina_stripe_payment_method_id.value = result.paymentMethod.id;
                                $('#mpk-form').STSendAjaxPackage();
                                break;
                        }
                    }
                });
            };

            $(function () {
                /* Modal */
                $(".booking_modal_form", 'body').on('st_wait_checkout_modal', function (e) {
                    var payment = $('input[name="st_payment_gateway"]:checked', this).val();
                    if (payment === 'vina_stripe') {
                        vina_tokenRequest('modal');
                        return false;
                    }
                    return true;
                });

                $(".booking_modal_form", 'body').on('st_before_checkout_modal', function (e) {
                    $('input[name="wait_validate_st_twocheckout"]', this).val('wait');
                    var check = true;
                    $('.stripe-card-form input.is-empty').removeClass('stripe-check-empty');
                    $('.stripe-card-form input.is-empty').each(function(){
                        var me = $(this);
                        if(me.val() == ''){
                            check = false;
                            me.addClass('stripe-check-empty');
                        }
                    })
                    if(!check){
                        return false;
                    }
                });
                /* End Modal */

                $('#cc-form','body').on('st_wait_checkout', function (e) {
                    var payment = $('input[name="st_payment_gateway"]:checked', this).val();
                    if (payment === 'vina_stripe') {
                        vina_tokenRequest('form');
                        return false;
                    }
                    return true;
                });
                $("#cc-form", 'body').on('st_before_checkout', function (e) {
                    $('input[name="wait_validate_st_stripe"]', this).val('wait');
                    var check = true;
                    $('.stripe-card-form input.is-empty').removeClass('stripe-check-empty');
                    $('.stripe-card-form input.is-empty').each(function(){
                        var me = $(this);
                        if(me.val() == ''){
                            check = false;
                            me.addClass('stripe-check-empty');
                        }
                    })
                    if(!check){
                        return false;
                    }
                });



                $("#mpk-form").submit(function(e) {
                    var payment = $('input[name="st_payment_gateway"]:checked', this).val();
                    if (payment === 'vina_stripe') {
                        e.preventDefault();
                        vina_tokenRequest('package');
                        return false;
                    }
                    return true;
                });
            });
        });
    </script>