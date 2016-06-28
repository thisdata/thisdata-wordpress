<?php

namespace ThisData\WordPress;

class JS {

    static function init() {

        \Analog::log('in JS::track',\Analog::DEBUG);

        wp_register_script('thisdata-plugin', plugin_dir_url(__FILE__).'js/thisdata.js',[], '1', true);

        add_action('login_enqueue_scripts', function(){
            static::track();
        });

        add_action( 'admin_enqueue_scripts', function(){
            static::trackCurrentUser();
        });
    }

    static function trackCurrentUser($params=null) {
        static::track([
            'user' => Events::getUser(Events::getCurrentUser())
        ]);
    }

    static function track($params=null) {

        \Analog::log('in JS::track',\Analog::DEBUG);

        if($JSWriteKey = API::getJSWriteKey() ) {

            \Analog::log('Tracking with jswritekey'.$JSWriteKey,\Analog::DEBUG);

            $data = [
                'apiKey' => $JSWriteKey,
            ];

            if($params) {
                $data += [
                    'signed' => $params,
                    'signature' => static::getHash($params)
                ];
            }

            wp_localize_script('thisdata-plugin', 'ThisDataPlugin', $data);

            wp_enqueue_script('thisdata-plugin');
        }
    }

    static function getHash($params) {
        return hash_hmac('sha256', json_encode($params), API::getJSSignature());
    }
}
