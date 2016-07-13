<?php
/*
Plugin Name: ThisData for WordPress
Plugin URI: https://thisdata.com/
Version: 1.1.3
Description: ThisData provides login intelligence. We notify you when one of your users or customers has their account accessed from somewhere unusual or by a device they don't normally use. It gives your customers the confidence that you're taking security seriously and doing everything you can to protect their account.
Text Domain: thisdata-plugin
License: GPL
*/
namespace ThisData\WordPress;

$composer_autoload = __DIR__.'/vendor/autoload.php';

if(file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

const ENV_API_KEY = 'THISDATA_API_KEY';
const ENV_JS_WRITE_KEY = 'THISDATA_JS_WRITE_KEY';
const ENV_JS_SIGNATURE = 'THISDATA_JS_SIGNATURE';
const ENV_WEBHOOK_SIGNATURE = 'THISDATA_WEBHOOK_SIGNATURE';


if(version_compare(\PHP_VERSION, '5.5', '<' )) {

    add_action('admin_notices', function() {
        $message = sprintf(
        sprintf(__('Sorry your PHP version is too old. The ThisData plugin requires PHP >= 5.5. You are running version %s.','thisdata-plugin'), \PHP_VERSION),
        Admin::getSettingsPageURL());

        echo '<div class="notice notice-info"><p>'.$message.'</p></div>';
    });

} else {

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
                JS::init();

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

}
