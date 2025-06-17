<?php
declare(strict_types=1);
namespace Inc\Base\Controller;
    
class BaseController{
    public $plugin_path;
	public $plugin_url;
	public $plugin;
    function __construct(){
        $this->plugin_path = plugin_dir_path( dirname( __FILE__, 3 ) );
		$this->plugin_url = plugin_dir_url( dirname( __FILE__, 3 ) );
		$this->plugin = plugin_basename( dirname( __FILE__, 3 ) ) . '/traveler-social-login.php';
    }

    
}