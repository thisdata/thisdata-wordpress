<?php
/**
 * Plugin Name: ThisData for WordPress
 * Version: 0.1
 * Description: ThisData provides login intelligence. We notify you when one of your users or customers has their account accessed from somewhere unusual or by a device they don't normally use. It gives your customers the confidence that you're taking security seriously and doing everything you can to protect their account.
 * Text Domain: thisdata-plugin
 */
namespace ThisData\WordPress;

spl_autoload_register( function($classname) {

     if(preg_match('#^ThisData\\\WordPress\\\#',$classname)){

         $path = preg_replace(['#^ThisData\\\WordPress#','#\\\#'],[__DIR__,'/'],$classname).'.php';

         if(file_exists($path)) {
             require $path;
         }
     }
});

const ENV_API_KEY = 'THISDATA_API_KEY';

if( is_admin() ) {
    $admin = new Admin();
    $admin->init();
    $admin->render();
}

add_action('init', function() {

    if($apiKey = API::getKey()) {

        try {

            Events::init(API::getEventsEndpoint());
            Webhook::init();

        } catch (\Exception $e) {

            add_action('admin_notices', function() use ($e) {
                $message = $e->getMessage();
                echo '<div class="notice notice-error"><p>'.$message.'</p></div>';
            });
        }

    } elseif(empty($_GET['page']) || $_GET['page'] !== Admin::SETTINGS) {

        add_action('admin_notices', function() {
            $message = sprintf(
            __('Almost done, please enter your <a href="%s">ThisData API Key</a> to complete the installation.','thisdata-plugin'),
            Admin::getSettingsPageURL());

            echo '<div class="notice notice-info"><p>'.$message.'</p></div>';
        });
    }

});
