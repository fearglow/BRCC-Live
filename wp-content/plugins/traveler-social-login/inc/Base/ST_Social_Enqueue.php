<?php 
namespace Inc\Base;
class ST_Social_Enqueue{
    public function register_st_social_enqueue() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ),99 );
    }
    
    function enqueue() {
        wp_enqueue_script( 'st-social-main', ST_SOCIAL_PLUGIN_URL . 'assets/main.js' ,array(), '', true );
        wp_enqueue_style( 'st-social-main', ST_SOCIAL_PLUGIN_URL . 'assets/style.css');
    }
}