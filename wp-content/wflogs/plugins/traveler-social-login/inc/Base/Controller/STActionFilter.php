<?php
declare(strict_types=1);
namespace Inc\Base\Controller;
use Inc\Base\Controller\BaseController;
class STActionFilter extends BaseController{
    function __construct(){
        add_action('before_body_content', array($this,'st_add_base_library_social'));
    }
    function st_add_base_library_social(){
        ob_start();
        ?>
        <script>
            function handleSocialLoginResult(rs) {
                if (rs.reload && typeof rs.reload !== 'undefined')
                    window.location.reload();
                if (rs.message)
                    alert(rs.message);
            }
            function sendLoginData(data) {
                data._s = st_params._s;
                data.action = 'traveler.socialLogin';
                var parent_login = jQuery(".login-regiter-popup");
                jQuery.ajax({
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    url: st_params.ajax_url,
                    beforeSend: function () {
                        parent_login.find('.map-loading').html('<div class="st-loader"></div>');
                        parent_login.find('.map-loading').css('z-index', 99);
                        parent_login.find('.map-loading').show();

                    },
                    success: function (rs) {
                        handleSocialLoginResult(rs);
                    },
                    error: function (e) {

                        alert('Can not login. Please try again later');
                    }
                })
            }

            function startLoginWithFacebook() {
                FB.getLoginStatus(function (response) {
                    if (response.status === 'connected') {
                        sendLoginData({
                            'channel': 'facebook',
                            'access_token': response.authResponse.accessToken
                        });

                    } else {
                        FB.login(function (response) {
                            if (response.authResponse) {
                                sendLoginData({
                                    'channel': 'facebook',
                                    'access_token': response.authResponse.accessToken
                                });

                            } else {
                                alert('User cancelled login or did not fully authorize.');
                            }
                        }, {
                            scope: 'email,public_profile',
                            return_scopes: true
                        });
                    }
                });
            }

            // Load the SDK asynchronously
			let lang = document.documentElement.lang;
			lang = lang.replace( '-', '_' );
            console.log(navigator.languages);

            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id))
                    return;
                js = d.createElement(s);
                js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
            window.fbAsyncInit = function () {
                FB.init({
                    appId: st_params.facbook_app_id,
                    cookie: true, // enable cookies to allow the server to access
                    // the session
                    xfbml: true, // parse social plugins on this page
                    version: 'v3.1' // use graph api version 2.8
                });

            };
        </script>
        <?php $content = @ob_get_clean();
        echo $content;
    }
}