<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Setting\Field;

class Admin extends \Mohiohio\WordPress\Admin
{
    protected $section_title = null;
    protected $capability = 'install_plugins';
    const SETTINGS = 'this-data-settings';
    const SETTINGS_API_KEY = 'api_key';
    const SETTINGS_JS_WRITE_KEY = 'js_write_key';
    const SETTINGS_JS_SIGNATURE = 'js_signature';
    const SETTINGS_WEBHOOK_SIGNATURE = 'webhook_signature';

    function get_page_name() {
        return 'ThisData';
    }

    function get_header() {
        return '<img src="'.plugin_dir_url(__FILE__).'assets/logo.png" title="ThisData" alt="ThisData" />';
    }

    function get_intro() {
        return '<p>This plugin will look for an environment variable before reading the below value.<br/>Using an environment variable instead of this field is the recommended approach.</p>';
    }

    static function getSettingsPageURL() {
        return admin_url('options-general.php?page='.self::SETTINGS);
    }

    static function display_env_set($env_key, $field) {

        if(getenv($env_key)){
            $field['props']['disabled'] = true;
            $field['props']['class'] .= ' disabled';
            $field['props']['placeholder'] = 'Set with environment variable';
            $field['type'] = 'text';
        }

        $field['display_callback'] = function($value, $props) use ($env_key) {
            echo Field::input($value,$props);
            echo '<br/><small>Environment Key: <strong>'.$env_key.'</strong></small>';
        };

        return $field;
    }

    function init() {

        $field = [
            'name'=> self::SETTINGS_API_KEY,
            'title'=>'Your API Key',
            'type' => 'password',
            'props' => ['class'=>'regular-text']
        ];

        $this->add_field(static::display_env_set(ENV_API_KEY,$field));

        $field = [
            'name'=> self::SETTINGS_JS_WRITE_KEY,
            'title'=>'JavaScript Write Key',
            'type' => 'password',
            'props' => ['class'=>'regular-text']
        ];

        $this->add_field(static::display_env_set(ENV_JS_WRITE_KEY,$field));

        /*
        $field = [
            'name'=> self::SETTINGS_JS_SIGNATURE,
            'title'=>'JavaScript Secret Signature',
            'type' => 'text',
            'props' => ['class'=>'regular-text']
        ];

        $this->add_field(static::display_env_set(ENV_JS_SIGNATURE,$field));*/

        $this->add_field([
            'name'=>'webhook',
            'title' => 'Webhook URL',
            'display_callback' => function() {
                echo "<pre>".home_url(Webhook::WEBHOOK_URL)."</pre>";
                echo "<p>When a suspicious login is detected ThisData will email the user to confirm if it was them. If they indicate that it was not them you can use this webhook alert path to automatically log out the user and reset the password. <a target=\"_blank\" href=\"http://help.thisdata.com/docs/webhooks\">Learn more about when and what we'll send.</a><p>";
            }
        ]);

        /*$field = [
            'name'=> self::SETTINGS_WEBHOOK_SIGNATURE,
            'title'=>'Secret for Webhook Signatures ',
            'type' => 'text',
            'props' => ['class'=>'regular-text']
        ];

        $this->add_field(static::display_env_set(ENV_WEBHOOK_SIGNATURE,$field));*/
    }

    static function get_settings_namespace() {
        return self::SETTINGS;
    }
}
