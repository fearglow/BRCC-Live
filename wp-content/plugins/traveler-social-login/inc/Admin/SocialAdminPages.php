<?php 
namespace Inc\Admin;
class SocialAdminPages{
    public function admin_init_func(){
		add_filter( 'social_setting', array($this, 'fnc_social_setting'), 10,1 );
    }
    public function fnc_social_setting( $data ) {
        $social_config = array(
			[
				'id' => 'social_fb_tab',
				'label' => __('Facebook', 'traveler-social-login'),
				'type' => 'tab',
				'section' => 'option_social'
			],
			[
				'id' => 'social_fb_login',
				'label' => __('Facebook Login', 'traveler-social-login'),
				'type' => 'on-off',
				'std' => 'on',
				'section' => 'option_social'
			],
			[
				'id' => 'social_fb_app_id',
				'label' => __('Facebook App ID', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'section' => 'option_social'
			],			[
				'id' => 'social_google_tab',
				'label' => __('Google', 'traveler-social-login'),
				'type' => 'tab',
				'section' => 'option_social'
			],
			[
				'id' => 'social_gg_login',
				'label' => __('Google Login', 'traveler-social-login'),
				'type' => 'on-off',
				'std' => 'on',
				'section' => 'option_social'
			],
			[
				'id' => 'social_gg_client_id',
				'label' => __('Client ID', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'section' => 'option_social'
			],
			[
				'id' => 'social_gg_client_secret',
				'label' => __('Client Secret', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'section' => 'option_social'
			],
			[
				'id' => 'social_gg_client_redirect_uri',
				'label' => __('Origin site URL', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'desc' => __('Example: http://yourdomain.com', 'traveler-social-login'),
				'section' => 'option_social'
			],
			[
				'id' => 'social_tw_tab',
				'label' => __('Twitter', 'traveler-social-login'),
				'type' => 'tab',
				'section' => 'option_social'
			],
			[
				'id' => 'social_tw_login',
				'label' => __('Twitter Login', 'traveler-social-login'),
				'type' => 'on-off',
				'std' => 'on',
				'section' => 'option_social'
			],
	
			[
				'id' => 'social_tw_client_id',
				'label' => __('API Key', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'section' => 'option_social'
			],
			[
				'id' => 'social_tw_client_secret',
				'label' => __('API Secret', 'traveler-social-login'),
				'type' => 'text',
				'std' => '',
				'section' => 'option_social'
			]
			
        );
    
        return array_merge( $data, $social_config );
    }
}