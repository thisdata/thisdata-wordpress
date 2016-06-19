<?php

namespace ThisData\WordPress;

class Admin extends \Mohiohio\WordPress\Admin
{
    protected $section_title = null;
    protected $capability = 'install_plugins';
    const SETTINGS = 'this-data-settings';
    const SETTINGS_API_KEY = 'api_key';

    function get_page_name() {
        return 'ThisData';
    }

    function get_header() {
        return '<img src="'.plugin_dir_url(__FILE__).'assets/logo.png" title="ThisData" alt="ThisData" />';
    }

    function get_intro() {
        return '<p>This plugin will look for an environment variable named <strong>'.\ThisData\WordPress\ENV_API_KEY.'</strong> before reading the below value.<br/>Using an environment variable instead of this field is the recommended approach.</p>';
    }

    static function getSettingsPageURL() {
        return admin_url('options-general.php?page='.self::SETTINGS);
    }

    function init() {

        $field = [
            'name'=> self::SETTINGS_API_KEY,
            'title'=>'Your API Key',
            'type' => 'password',
            'props' => ['class'=>'regular-text']
        ];

        if(getenv(ENV_API_KEY)){
            $field['props']['disabled'] = true;
            $field['props']['class'] .= ' disabled';
            $field['props']['placeholder'] = 'Set with environment variable';
        }

        $this->add_field($field);

        $this->add_field([
            'name'=>'webhook',
            'title' => 'Alert Webhooks Path',
            'display_callback' => function() {
                echo "<p>This optional feature enables ThisData to automatically log out and reset user passwords when we generate an alert. <a target=\"_blank\" href=\"http://help.thisdata.com/docs/webhooks\">Learn more about when and what we'll send.</a><p>";
                echo "<pre>".home_url(Webhook::WEBHOOK_URL)."</pre>";
            }
        ]);
    }

    static function get_settings_namespace() {
        return self::SETTINGS;
    }
}
