<?php
/* ======================================================
 # Login as User for WordPress - v1.6.0 (free version)
 # -------------------------------------------------------
 # Author: Web357
 # Copyright © 2014-2024 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https://www.web357.com/login-as-user-wordpress-plugin
 # Demo: https://login-as-user-wordpress-demo.web357.com/wp-admin/
 # Support: https://www.web357.com/support
 # Last modified: Wednesday 02 April 2025, 11:37:26 PM
 ========================================================= */
// Settings page
?>
<div class="wrap">
	<h1><?php echo $this->plugin_name; ?> v<?php echo $this->version; ?></h1>
    <div class="lau-settings">
        <div class="lau-about">
            <h2>
                <?php echo esc_html__( 'About', 'login-as-user' ); ?> Login as User   (Free Version)    
            </h2>

            <div style="margin-top: 20px; overflow:hidden;">
                <a href="https://www.web357.com/login-as-user-wordpress-plugin?utm_source=SettingsPage&utm_medium=ReadMoreLink&utm_content=loginasuserwp&utm_campaign=read-more" target="_blank">
                    <img class="lau-product-img" src="<?php echo esc_url( plugins_url( 'img', (__FILE__) ) ); ?>/login-as-user-wordpress-plugin-120x200.png" alt="Login as User WordPress plugin by Web357" />
                </a>
                <p>The Login as a User WordPress plugin allows admins to have easy access to the frontend as a specific user and thus solve problems or provide better and faster customer support. With one click, the admin logs in as the external user or customer and handles any situation without wasting any time at all. If you want a WordPress plugin to switch accounts in an instant, Login as User is for you. <a href="https://www.web357.com/login-as-user-wordpress-plugin?utm_source=SettingsPage&utm_medium=ReadMoreLink&utm_content=loginasuserwp&utm_campaign=read-more" target="_blank">Read more &raquo;</a></p>
                
            </div>

            <div class="lau-free-vs-pro" style="margin-top: 20px;">
            <hr> 
                <h4>Unlock Premium Features with Login as User Pro</h4>
                 
                <p>Enhance your WordPress site management with premium features available only in the Pro version of the Login as User plugin. Upgrade to gain advanced capabilities and superior control for seamless administration.</p>
                 
                
                <table>
                    <tr>
                        <th>Features</th>
                          <th>Free</th> 
                        <th>Pro</th>
                    </th>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">Display the Login as User in All Users Page in Admin</div>
                            <div class="lau-feature-desc">In the Admin area, you select a user from the list and click the ‘Login as User’ link to switch to that user.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-tick"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">User’s Profile Page</div>
                            <div class="lau-feature-desc">Are you in a user’s profile and want to login as this user? Just click the button Login as:… at the top left-hand side and you will be able to check data and help this specific user with any problem.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-tick"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">View WooCommerce Orders Page</div>
                            <div class="lau-feature-desc">Are you using the WooCommerce plugin? In the WooCommerce orders page, the Login as user button appears besides each customer to help you provide better customer support.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">Check WooCommerce Order Details</div>
                            <div class="lau-feature-desc">Is one of your customers having trouble with their order? Do you want to check the details of a customer’s order? You can easily check the customer’s problem from his/her perspective by switching with the Login as User button in the WooCommerce order details page.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">Full View of the WooCommerce Subscriptions Page</div>
                            <div class="lau-feature-desc">The Login as User button of each subscriber appears next to their name in the WooCommerce Subscriptions Page. Just click on it to switch.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">WooCommerce Subscription Details Page</div>
                            <div class="lau-feature-desc">You can easily control every subscriber’s data by switching accounts on the WooCommerce subscription details page. You simply click the Login as User button displayed at the right sidebar as a metabox to see the subscriber’s details and make any changes necessary.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">Shortcode for “Login as User”</div>
                            <div class="lau-feature-desc">The &#91;login_as_user&#93; shortcode allows you to add a "Login as User" button to any post, page, or widget on your WordPress site. This feature facilitates easy and direct login as a specific user, which is particularly useful for administrators who need to quickly view or manage the site from another user's perspective. Learn more <a target="_blank" href="https://docs.web357.com/article/102-shortcode-login-as-user">here</a>.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                    <tr>
                        <td class="lau-feature-info">
                            <div class="lau-feature-title">Role Management Permissions in Login as User Plugin</div>
                            <div class="lau-feature-desc">Define which roles can log in as users of other roles, enhancing security and control by limiting this capability to specific roles. Learn more <a target="_blank" href="https://docs.web357.com/article/118-role-management-permissions-in-login-as-user-plugin-pro-only">here</a>.</div>
                        </td>
                         <td><span class="lau-icon lau-icon-x"></span></td> 
                        <td><span class="lau-icon lau-icon-tick"></span></td>
                    </tr>
                </table>

                
                 
                <div class="lac-buy-pro-btn-container">
                    <a href="https://www.web357.com/login-as-user-wordpress-plugin?utm_source=SettingsPage&utm_medium=BuyProLink&utm_content=loginasuserwp&utm_campaign=upgrade-pro" class="button lac-buy-pro-btn" target="_blank">Upgrade to PRO</a>
                </div>
                 
            </div>

            <div style="margin-top: 20px;">
            <hr> 
                <h4><?php echo esc_html__( 'Need support?', 'login-as-user'); ?></h4>
                <?php
                echo sprintf(
                    __( '<p>If you are having problems with this plugin, please <a href="%1$s">contact us</a> and we will reply as soon as possible.</p>', 'login-as-user' ),
                    esc_url( 'https://www.web357.com/support' )
                );
                ?>
            </div>

            <div style="margin-top: 20px;" class="lac-developed-by">
            <hr> 
                <span><?php echo __('Developed by', 'login-as-user'); ?></span>
                <a href="<?php echo esc_url('https://www.web357.com/'); ?>" target="_blank">
                    <img src="<?php echo esc_url( plugins_url( 'img', (__FILE__) ) ); ?>/web357-logo.png" alt="Web357 logo" />
                </a>
            </div>

        </div>
        <div class="lau-form">
            <h2>
                <?php echo esc_html__( 'How it works?', 'login-as-user' ); ?>
            </h2>
            <?php echo wp_kses( __( '<p style="color:red">You have to navigate to the <a href="users.php"><strong>Users page</strong></a> and then you will see a button with the name "<strong>Login as: `username`</strong>", at the right side of each username. If you click on this button you will login at the front-end of the website as this User.</p>', 'login-as-user' ), array( 'strong' => array(), 'br' => array(), 'p' => array(), 'a' => array('href'=>array()) ) ); ?>

            <h2 style="margin-top: 40px;">
                <?php echo esc_html__( 'Settings', 'login-as-user' ); ?>
            </h2>
            <form action="options.php" method="post">
                <?php settings_fields( 'login-as-user' ); ?>
                <?php do_settings_sections( 'login-as-user' ); ?>
                <?php submit_button( esc_html__( 'Save Settings', 'login-as-user' ) ); ?>
            </form>
        </div>
    </div>
</div>