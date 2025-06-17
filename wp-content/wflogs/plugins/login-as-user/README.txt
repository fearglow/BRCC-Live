=== Login as User ===
Contributors: yiannistaos, johnbillion
Tags: user, login, admin, login as user, web357
Donate link: https://www.paypal.me/web357
Requires at least: 5.3
Tested up to: 6.5
Requires PHP: 7.3
Stable tag: 1.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Login as User is a free WordPress plugin that helps admins switch user accounts instantly to check data.

== Description ==
[Login as a User WordPress plugin](https://www.web357.com/product/login-as-user-wordpress-plugin) allows admins to have easy access to the frontend as a specific user and thus solve problems or provide better and faster customer support. With one click, the admin logs in as the external user or customer and handles any situation without wasting any time at all. If you want a WordPress plugin to switch accounts in an instant, Login as User is for you.

== Video Description ==

[youtube https://www.youtube.com/watch?v=OrptAiAQo04]

== Demo ==

[Live Demo](https://demo.web357.com/wordpress/login-as-user/wp-admin/)

== Basic Features ==
- In the Admin area, you select Users in the left-hand side menu and click All Users in the sub-menu. Now, all users of your website appear on the screen along with the Login as… button besides each name. You can click the button of the user you want to switch account.
- Are you in a user’s profile and want to login as this user? Just click the button Login as:… at the top left-hand side and you will be able to check data and help this specific user with any problem.
- You can choose the position of the "Login as user" toolbar. There are two available positions, the top and the bottom.
- Compatible with the "User Insights" WordPress plugin. You just have to add a custom field with the name "loginasuser".

== PRO Features ==
- Are you using the WooCommerce plugin? In the WooCommerce orders page, the Login as user button appears besides each customer to help you provide better customer support.
- Is one of your customers having trouble with their order? Do you want to check the details of a customer’s order? You can easily check the customer’s problem from his/her perspective by switching with the Login as User button in the WooCommerce order details page.
- You can use the shortcode [login_as_user user_id="357"] to display the login as user button everywhere, event at the frontend. You just have to specify the user ID in the attribute.

== Installation ==
The plugin is simple to install:

1. Download the file `login-as-user.zip`.
2. Unzip it.
3. Upload `login-as-user` directory to your `/wp-content/plugins` directory.
4. Go to the plugin management page and enable the plugin.
5. Configure the options from the `Settings > Login as User` page

== Frequently Asked Questions ==
= Why would I want to use this plugin? =

This plugin is helpful if you are an admin and you want to sign in as any user, or if you would like to check and confirm if the users see the correct data into their account page. This plugin prevent admins to ask for login details (username and password).

== Screenshots ==
1. In the Admin area, you select Users in the left-hand side menu and click All Users in the sub-menu. Now, all users of your website appear on the screen along with the Login as… button besides each name. You can click the button of the user you want to switch account.
2. Are you in a user’s profile and want to login as this user? Just click the button Login as:… at the top left-hand side and you will be able to check data and help this specific user with any problem.
3. Are you using the WooCommerce plugin? In the WooCommerce orders page, the Login as user button appears besides each customer to help you provide better customer support.
4. Is one of your customers having trouble with their order? Do you want to check the details of a customer’s order? You can easily check the customer’s problem from his/her perspective by switching with the Login as User button in the WooCommerce order details page.
5. Settings.

== Changelog ==
= 01-Jul-2024 : v1.5.2 =
* [Bug Fixed] PHP Fatal error: Uncaught Error: Call to undefined method WP_Post::get_customer_id() in /wp-content/plugins/login-as-user-pro/includes/class-w357-login-as-user.php:796

= 28-Jun-2024 : v1.5.1 =
* Fully compatible with WooCommerce v9.0.x and WooCommerce Subscriptions v6.4.x
* [New Feature]: Role Management Permissions in Login as User Plugin. Define which roles can log in as users of other roles, enhancing security and control by limiting this capability to specific roles. Learn more https://docs.web357.com/article/118-role-management-permissions-in-login-as-user-plugin-pro-only
* [New Feature]: Added option to display the admin link in the topbar.
* Fixed 'Login as User' column compatibility with WooCommerce Subscriptions 6.4.
* [Bug Fix] Enhanced order column compatibility using WooCommerce's get_customer_id method.
* [UI] Implemented minor fixes and improvements for a smoother user experience, including a go-back link in the WordPress top navbar.
* Added button_name parameter to [login_as_user] shortcode for customizable button text, including support for dynamic user type placeholder  (e.g., [ login_as_user user_id="357" redirect_to="/my-account" button_name="Login as "]). [Thank you, Giorgos Iordanidis] Documentation updated: https://docs.web357.com/article/102-shortcode-login-as-user
* [Improvement] The default value for the message_display_position should be "bottom", not "top".
* Minor fixes and Improvements.

= 26-Apr-2024 : v1.5.0 =
* Improved handling of user_id and redirect_to parameters in the [login_as_user] shortcode for enhanced functionality. Documentation updated: https://docs.web357.com/article/102-shortcode-login-as-user

= 18-Apr-2024 : v1.4.9 =
* Fully compatible with WordPress v6.5.x and WooCommerce v8.8.x
* CSS Bug fixed: The "Login as User" button is currently overflowing its column within the table. [Thank you, Anthony Grullon]
* Bug fixed: After clicking the button “go back to admin as..” a 404 error occurred. It does not give the correct URL. It mostly happens when the WordPress is in a subdirectory [Many thanks to )]
* Introduced a new attribute for the [login_as_user] shortcode, enabling administrators to redirect users to a specific page after logging in. Example usage: [login_as_user user_id="1" redirect_to="/my-account"].
* Now supports WooCommerce High-Performance Order Storage (HPOS) [Thank you, Rein Ridder]
* Ensured functionality remains intact when WordPress is given its own directory, following the guidelines provided https://wordpress.org/documentation/article/giving-wordpress-its-own-directory/ [Thanks, James]
* Implemented minor fixes and improvements for a smoother user experience.

= 23-Oct-2023 : v1.4.8 =
* Fully compatible with WordPress v6.3.x and WooCommerce v8.2.x
* Minor fixes and improvements

= 27-Jul-2023 : v1.4.7 =
* Added PHP 8.2 Compatibility to the Plugin Update Checker and fix deprecation notices regarding PHP 8.2.x.
* Fix CSS issues regarding z-index on Divi themes.

= 26-Jul-2023 : v1.4.6 =
* [PHP 8.2 Deprecated Warning]: Creation of dynamic property LoginAsUser_AdminPro:: is deprecated in /wp-content/plugins/login-as-user-pro/admin/class-admin.php on line 51 on PHP 8.2, and WordPress 6.2.2.

= 07-Jun-2023 : v1.4.5 =
* [Compatibility] Fully compatible with WordPress v6.2.x and WooCommerce v7.7.x
* Compatible with the WooCommerce Mobile App
* After login as a user, in the notification bar show the email instead of username. Example: "go back to admin as Yiannis Christodoulou (yiannis [@] web357 [.] com)"
* Bug fixed after going back to admin dashboard. Bug message: "The link you followed as expired." 
* PHP Warning fixed: Undefined array key "SERVER_NAME" in .../wp-content/plugins/login-as-user-pro/login-as-user-pro.php on line 67
* Minor fixes and improvements

= 14-Jun-2022 : v1.4.4 =
* Minor bugfix: The "Login as User" button is missing for specific user roles.

= 14-Jun-2022 : v1.4.3 =
* [Compatibility] Fully compatible with WordPress v6.0 and WooCommerce v6.5+
* Minor fixes and improvements

= 11-Feb-2022 : v1.4.2 =
* [New Feature] You can use the shortcode [login_as_user user_id="357"] to display the login as user button everywhere, event at the frontend. You just have to specify the user_id. Do not forget to replace the 357 with the user ID you want. (This feature included only in the Premium version)
* [Compatibility] Fully compatible with WordPress v5.9 and WooCommerce v6.2
* Minor fixes and improvements

= 28-Jul-2021 : v1.4.1 =
* [Styling improvement] To fix the display of the login as user button when you have a lot of columns in Users/Orders area, we 've created a new option to decrease the width of "Login as User" column. Navigate to: Settings > Login as User > "Login as...«option»" button	> None (display only the user icon). [Thank you, Robert]
* [Compatibility] Fully compatible with WordPress v5.8 and WooCommerce v5.5
* Minor fixes and improvements

= 08-Apr-2021 : v1.4.0 =
* [Compatibility] Fully compatible with the User Insights WordPress plugin.
* [Compatibility] Fully compatible with WordPress v5.7 and WooCommerce v5.1.x
* Minor fixes and improvements

= 09-Jan-2021 : v1.3.0 =
* [Compatibility] Fully compatible with Loco Translate plugin.
* [New feature] Validate website license key in the plugin settings  (only for the premium version).
* [Compatibility] Fully compatible with WordPress v5.6 and WooCommerce v4.8.

= 19-Oct-2020 : v1.2.2 =
* [New option] You can now choose the position of the "Login as user" toolbar. There are two available positions, the TOP and the BOTTOM.
* [Compatibility] Fully compatible with WordPress 5.5 and WooCommerce 4.6.

= 15-Apr-2020 : v1.2.1 =
* [Bug Fixed] Error with the redirection URL after a successful login attempt. Admin is redirected to a URL that contains twice the value of the function "home_url()", and gives a 404 error.

= 30-Mar-2020 : v1.2.0 =
* [Style Improvement] Remove any margin of the button that displayed on the toolbar at frontend.
* [Bug Fixed] The "login as user" button is not displayed. There was a filter conflict because a 3rd plugin calls the "manage_users_custom_column" filter too. We have change the priority and the issue has been resolved. [Many thanks to Michael Kuhlman for his help]
* [Bug Fixed] Error with the redirection URL. Admin is not redirected to the correct page from settings, after logged in as a user.

= 14-Feb-2020 : v1.1.0 =
* [New feature] You can now choose from the settings, which string will be displayed on the "Login as User" button. You can choose one from the following, nickname, or first name, or last name, or full name. For example Login as «Yiannis», or log in as «Christodoulou», or log in as «Johnathan99», or log in as «Yiannis Christodoulou».
* [New Feature] Show only the first X characters of the username, or first/last name, or full name, on the "Login as...«option»" button. For example, if you choose 5, the button will be displayed like this: Login as «Yiann...», or Login as «Chris...», or Login as «Johna...», or Login as «Yiann...».
* [Bug Fixed] Do not load the files "public.min.css" and "public.min.js" if the Login as User functionality is not enabled.
* Minor bug fixes and improvements.

= 29-Jul-2019 : v1.0.1 =
* If an admin is already logged in, a short message replaces the button. Example: "Already logged in" instead of "---".
* The language files have been updated with new strings.

= 11-Jul-2019 : v1.0.0 =
* First beta release