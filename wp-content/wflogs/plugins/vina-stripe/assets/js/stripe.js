jQuery(function ($) {
	var old_order_id = !1;
	var new_nonce = !1;
	$.fn.STSendAjaxPackage = function () {
		this.each(function () {
			var me = $(this);
			var button = $('#st_submit_member_package', this);
			var data = me.serializeArray();
			data.push({
				name: 'action',
				value: 'booking_form_package_direct_submit'
			});
			me.find('.form-control').removeClass('error');
			me.find('.form_alert').addClass('hidden');
			var dataobj = {};
			for (var i = 0; i < data.length; ++i) {
				dataobj[data[i].name] = data[i].value
			}
			dataobj.order_id = old_order_id;
			button.addClass('loading');
			console.log($("#mpk-form").serialize());
			$.ajax({
				type: 'post',
				url: st_params.ajax_url,
				data: dataobj,
				dataType: 'json',
				beforeSend: function () {
					$("#mpk-form .submit_payment").addClass('loading');
				},
				success: function (data) {
					if (data.redirect_url) {
						window.location.href = data.redirect_url;
					}
					if (data.redirect_form) {
						window.location.href = data.redirect_form;
					}
					var stripePublishKey = st_vina_stripe_params.vina_stripe.publishKey;
					if (st_vina_stripe_params.vina_stripe.sanbox == 'sandbox') {
						stripePublishKey = st_vina_stripe_params.vina_stripe.testPublishKey
					}
					var stripe = Stripe(stripePublishKey);
					if (data.error) {
						if (data.respon) {
							$('#mpk-form').find('.mt20').addClass('alert alert-danger').html(data.respon);
						} else {
							$('#mpk-form').find('.mt20').addClass('alert alert-danger').html(data.error);
						}

					}
					if (typeof (data.payment_intent_client_secret) != 'undefined' && data.payment_intent_client_secret) {
						stripe.handleCardAction(
							data.payment_intent_client_secret
						).then(function (result) {
							console.log(result);
							if (result.error) {
								alert(result.error.message);
							} else {

								$.ajax({
									url: st_params.ajax_url,
									dataType: 'json',
									type: 'POST',
									data: {
										'action': 'vina_stripe_package_confirm_server',
										'st_order_id': data.order_id,
										'payment_intent_id': result.paymentIntent.id,
										'data_step2': data,
									},
									beforeSend: function () {
										$("#mpk-form .submit_payment").addClass('loading');
									},
									success: function (response_server) {
										console.log("stripe server confirm");
										if (response_server.data.redirect_form) {
											window.location.href = response_server.data.redirect_form;
										}
									},
									complete: function (res) {
										if (typeof (res.data.st_order_id) != 'undefined' && res.data.st_order_id) {
											old_order_id = res.data.st_order_id
										}
										if (res.data.message) {
											me.find('.form_alert').addClass('alert-danger').removeClass('hidden');
											me.find('.form_alert').html(res.data.message)
										}
										if (res.data.message) {
											me.find('.mt20').addClass('alert-danger').removeClass('hidden');
											me.find('.mt20').html(res.data.message)
										}
										if (res.data.redirect) {
											window.location.href = res.data.redirect
										}
										if (res.data.redirect_url) {
											window.location.href = res.data.redirect_url
										}
										console.log(es.data.redirect_form);
										if (res.data.redirect_form) {
											window.location.href = res.data.redirect_form;
										}
										if (data.new_nonce) {

										}
										var widget_id = 'st_recaptchar_' + dataobj.item_id;
										//get_new_captcha(me);
										button.removeClass('loading');
										$("#mpk-form .submit_payment").removeClass('loading');
									},
								});
							}
						});
					} else {
						if (typeof (data.order_id) != 'undefined' && data.order_id) {
							old_order_id = data.order_id
						}
						if (data.message) {
							me.find('.form_alert').addClass('alert-danger').removeClass('hidden');
							me.find('.form_alert').html(data.message)
						}
						if (data.redirect) {
							window.location.href = data.redirect;
						}

						if (data.redirect_form) {
							$('body').append(data.redirect_form);
						}
						if (data.redirect_form) {
							window.location.href = data.redirect_form;
						}
						var widget_id = 'st_recaptchar_' + dataobj.item_id;
						//(me);
						button.removeClass('loading')
					}
					$("#mpk-form .submit_payment").removeClass('loading');
				},
				error: function (e) {
					button.removeClass('loading');
					alert('Lost connect to server');
					////(me)
				}
			});
		});
	};
	$("#mpk-form").submit(function (e) {
		var form = $(this);
		form.trigger('st_before_checkout');

		var payment = $('input[name="st_payment_gateway"]:checked', form).val();
		if (payment == 'vina_stripe') {
			var wait_validate = $('input[name="wait_validate_' + payment + '"]', form).val();
			if (wait_validate === 'wait') {
				form.trigger('st_wait_checkout');
				return false;
			}
			form.STSendAjaxPackage();
		}

	});

	function get_new_captcha(me) {
		var captcha_box = me.find('.captcha_box');
		url = captcha_box.find('.captcha_img').attr('src');
		captcha_box.find('.captcha_img').attr('src', url)
	}
});