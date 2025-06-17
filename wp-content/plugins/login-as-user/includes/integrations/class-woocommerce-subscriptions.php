<?php
if (!defined('WPINC')) {
    die;
}

class LoginAsUser_WooCommerce_Subscriptions_Integration {
    private $main;
    
    public function __construct($main) {
        $this->main = $main;
    }
    
    public function init() {
        add_filter('manage_edit-shop_subscription_columns', array($this, 'loginasuser_col'), 1000);
        add_action('manage_shop_subscription_posts_custom_column', array($this, 'loginasuser_col_content_hpos'), 10, 2);
        add_filter('woocommerce_shop_subscription_list_table_columns', array($this, 'loginasuser_col'), 1000);
        add_action('woocommerce_shop_subscription_list_table_custom_column', array($this, 'loginasuser_col_content_hpos'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_login_as_user_metabox'));
    }

    public function loginasuser_col($columns) {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('order_title' === $column_name) {
                $new_columns['loginasuser_col'] = __('Login as User', 'login-as-user');
            }
        }
        return $new_columns;
    }

    public function loginasuser_col_content_hpos($column, $subscription_id) {
        if ('loginasuser_col' === $column) {
            

             
            echo $this->main->onlyInProTextLink();
             
        }
    }

    public function add_login_as_user_metabox() {
        add_meta_box('login_as_user_metabox', __('Login as User'), array($this, 'login_as_user_metabox'), 'woocommerce_page_wc-orders--shop_subscription', 'side', 'core');
    }

    public function login_as_user_metabox($post) {
        

         
        echo $this->main->onlyInProTextLink();
         
    }
}