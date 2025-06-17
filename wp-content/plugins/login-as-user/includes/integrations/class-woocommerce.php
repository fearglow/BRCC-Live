<?php
if (!defined('WPINC')) {
    die;
}

class LoginAsUser_WooCommerce_Integration {
    private $main;
    
    public function __construct($main) {
        $this->main = $main;
    }
    
    public function init() {
        add_filter('manage_edit-shop_order_columns', array($this, 'loginasuser_col'), 1000);
        add_action('manage_shop_order_posts_custom_column', array($this, 'loginasuser_col_content'));
        add_filter('woocommerce_shop_order_list_table_columns', array($this, 'loginasuser_col'), 1000); 
        add_action('woocommerce_shop_order_list_table_custom_column', array($this, 'loginasuser_col_content_hpos'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_login_as_user_metabox'));
    }

    public function loginasuser_col($columns) {

        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;
            if ('order_number' === $column_name || 'order_title' === $column_name) {
                $new_columns['loginasuser_col'] = __('Login as User', 'login-as-user');
            }
        }
        return $new_columns;
    }

    public function loginasuser_col_content($column) {

        if ('loginasuser_col' === $column) {
            

             
            echo $this->main->onlyInProTextLink();
             
        }
    }

    public function loginasuser_col_content_hpos($column, $order_id) {
        if ('loginasuser_col' === $column) {
            

             
            echo $this->main->onlyInProTextLink();
             
        }
    }

    public function add_login_as_user_metabox() {
        add_meta_box('login_as_user_metabox', __('Login as User'), array($this, 'login_as_user_metabox'), 'shop_order', 'side', 'core');
        add_meta_box('login_as_user_metabox', __('Login as User'), array($this, 'login_as_user_metabox'), 'woocommerce_page_wc-orders', 'side', 'core');
    }

    public function login_as_user_metabox($post) {
        

         
        echo $this->main->onlyInProTextLink();
         
    }

    /**
	 * Instructs WooCommerce to forget the session for the current user, without deleting it.
	 *
	 * @param WooCommerce $wc The WooCommerce instance.
	 */
	public static function forget_woocommerce_session(WooCommerce $wc)
	{
		if (!property_exists($wc, 'session')) {
			return false;
		}

		if (!method_exists($wc->session, 'forget_session')) {
			return false;
		}

		$wc->session->forget_session();
	}
}