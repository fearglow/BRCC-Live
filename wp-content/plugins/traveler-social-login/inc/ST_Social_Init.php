<?php 
namespace Inc;
final class ST_Social_Init
{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function get_services() 
	{
 
		return [
			Base\ST_Social_Enqueue::class,
			Base\Controller\STSocialLogin::class,
			Base\Controller\STActionFilter::class,
			
			
			Admin\SocialAdminPages::class,
			ST_Social_Activate::class,
			ST_Social_Deactivate::class,
		];
    
	}

	public static function register_services() 
	{
		foreach ( self::get_services() as $class ) {
			
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'admin_init_func' ) ) {
				$service->admin_init_func();
			}
			if ( method_exists( $service, 'register_st_social_enqueue' ) ) {
				$service->register_st_social_enqueue();
			}
			
		}
	}
    private static function instantiate( $class )
	{
		$service = new $class();
		return $service;
	}
}
?>