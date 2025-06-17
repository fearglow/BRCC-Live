//Social Login
jQuery(function ($) {
    var startApp = function () {
        var key = st_social_params.google_client_id;
        let r = (Math.random() + 1).toString(36).substring(7);
        gapi.load('auth2', function () {
            auth2 = gapi.auth2.init({
                client_id: key,
                cookiepolicy: 'single_host_origin',
                plugin_name: 'single_host_origin' + r,
            });
            console.log(auth2);
            const google_login_1 = document.getElementById('st-google-signin2');
            const google_login_2 = document.getElementById('st-google-signin3');
            if(google_login_1){
                attachSignin(document.getElementById('st-google-signin2') , auth2);
            }
            if(google_login_2){
                attachSignin(document.getElementById('st-google-signin3'), auth2);
            }
        });
    };
    if (typeof window.gapi != 'undefined') {
        startApp();
    }

    function attachSignin(element , auth2) {
        auth2.attachClickHandler(element, {},
            function (googleUser) {
                var profile = googleUser.getBasicProfile();
                startLoginWithGoogle(profile);

            },
            function (error) {
                console.log(JSON.stringify(error, undefined, 2));
            });
    }

    function startLoginWithGoogle(profile) {
        if (typeof window.gapi.auth2 == 'undefined')
            return;
        sendLoginData({
            'channel': 'google',
            'userid': profile.getId(),
            'username': profile.getName(),
            'useremail': profile.getEmail(),
        });
    }

    function startLoginWithFacebook(btn) {
        btn.addClass('loading');
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
                    scope: 'email',
                    return_scopes: true
                });
            }
        });
    }

    function sendLoginData(data) {
        data._s = st_params._s;
        data.action = 'traveler.socialLogin';
        var parent_login = $(".login-regiter-popup");
        $.ajax({
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

    function handleSocialLoginResult(rs) {
        if (rs.reload && typeof rs.reload !== 'undefined')
            window.location.reload();
        if (rs.message)
            alert(rs.message);
    }

    $('.st_login_social_link').on('click', function () {
        var channel = $(this).data('channel');

        switch (channel) {
            case "facebook":
                startLoginWithFacebook($(this));
                break;
        }
    })

    /* Fix social login popup */
    function popupwindow(url, title, w, h) {
        var left = (screen.width / 2) - (w / 2);
        var top = (screen.height / 2) - (h / 2);
        return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    }

    $('.st_login_social_link').on('click', function () {
        var href = $(this).attr('href');
        if ($(this).hasClass('btn_login_tw_link'))
            popupwindow(href, '', 600, 450);
        return false;
    });
    /* End fix social login popup */

    function handleCredentialResponse(response) {
        console.log("Encoded JWT ID token: " + response.credential);
        const responsePayload = decodeJwtResponse(response.credential);

        console.log("ID: " + responsePayload.sub);
        console.log('Full Name: ' + responsePayload.name);
        console.log('Given Name: ' + responsePayload.given_name);
        console.log('Family Name: ' + responsePayload.family_name);
        console.log("Image URL: " + responsePayload.picture);
        console.log("Email: " + responsePayload.email);

        sendLoginData({
            'channel': 'google',
            'userid': responsePayload.sub,
            'username': responsePayload.name,
            'useremail': responsePayload.email,
        });
    }

    function decodeJwtResponse(token) {
        var base64Url = token.split(".")[1];
        var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        var jsonPayload = decodeURIComponent(
            atob(base64)
            .split("")
            .map(function (c) {
                return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );

        return JSON.parse(jsonPayload);
    }
    if(typeof google !== "undefined" && typeof google.accounts !== "undefined"){
        window.onload = function () {
            google.accounts.id.initialize({
                client_id: st_social_params.google_client_id,
                callback: handleCredentialResponse
            });
            const nodeList_SigIn = document.querySelectorAll(".buttonDiv");
            for (let i = 0; i < nodeList_SigIn.length; i++) {
                google.accounts.id.renderButton(
                    nodeList_SigIn[i], {
                        theme: "outline",
                        size: "large",
                    } // customization attributes

                );
            }
            const nodeList_SigUp = document.querySelectorAll(".buttonDivSignUp");
            for (let i = 0; i < nodeList_SigUp.length; i++) {
                google.accounts.id.renderButton(
                    nodeList_SigUp[i], {
                        theme: "outline",
                        size: "large",
                    } // customization attributes

                );
            }

            google.accounts.id.prompt(); // also display the One Tap dialog
        }
    }


});


