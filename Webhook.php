<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Router;

class Webhook {

    const WEBHOOK_URL = 'thisdata/webhook';

    static function init() {

        Router::routes([

            self::WEBHOOK_URL => function() {

                \Analog::log('With $_POST '.var_export($_POST,true), \Analog::DEBUG);
                \Analog::log('With Reqest body '.file_get_contents('php://input'), \Analog::DEBUG);
                \Analog::log('With Hash '.static::getHash(), \Analog::DEBUG);
                \Analog::log('With Header Hash '.$_SERVER['HTTP_X-Signature'], \Analog::DEBUG);

                if(!static::authenticate()){
                    header('HTTP/1.0 401 Unauthorized');
                    exit();
                }

                if(!isset($_POST['user_id'])){
                    return false;
                }

                $user_id = $_POST['user_id'];

                if(!$user = get_userdata($user_id)){
                    return false;
                }

                //Track this event ( custom event )
                Events::track([
                    'verb' => 'webhook-reset',
                    'eventEndpoint' => API::getEventsEndpoint(),
                    'user' => $user
                ]);

                //Destory sessoins,
                //\Analog::log('Destroying Sessions',\Analog::DEBUG);

                $sessions = \WP_Session_Tokens::get_instance( $user_id );
                $sessions->destroy_all();

                //Create new password
                wp_set_password(wp_generate_password(), $user_id);

                $key = get_password_reset_key($user);

                //Email user with Reset password link
                Email::passwordReset($user->user_email, $user->user_login, $key);
            }
        ]);
    }

    static function authenticate() {
        return isset($_SERVER['HTTP_X-Signature']) && $_SERVER['HTTP_X-Signature'] === static::getHash();
    }

    static function getHash() {

        $requestBody = file_get_contents('php://input');

        return hash_hmac('sha256', $requestBody, API::getKey());
    }
}
