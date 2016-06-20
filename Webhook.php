<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Router;

class Webhook {

    const WEBHOOK_URL = 'thisdata/webhook';

    static function init() {

        Router::routes([

            self::WEBHOOK_URL => function() {

                //\Analog::log('With $_POST '.var_export($_POST,true), \Analog::DEBUG);
                //\Analog::log('With Reqest body '.file_get_contents('php://input'), \Analog::DEBUG);
                //\Analog::log('With Hash '.static::getHash(), \Analog::DEBUG);
                //\Analog::log('With Header Hash '.$_SERVER['HTTP_X_SIGNATURE'], \Analog::DEBUG);

                if(!static::authenticate()){
                    header('HTTP/1.0 401 Unauthorized');
                    exit();
                }

                $data = json_decode(static::getRequestBody(),true);

                $user_id = $data['user']['id'];
                $was_user = $data['was_user'];

                if(!$user = get_userdata($user_id)){
                    return false; //No such user
                }

                if($was_user) {
                    return; //All Good, user confirmed that this was them
                }

                //Track this event ( custom event )
                Events::track([
                    'verb' => 'webhook-reset',
                    'eventEndpoint' => API::getEventsEndpoint(),
                    'user' => $user
                ]);

                //Destory sessoins,
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

    static function getRequestBody() {
        return file_get_contents('php://input');
    }

    static function getHash() {
        return hash_hmac('sha512', static::getRequestBody(), API::getKey());
    }

    static function authenticate() {
        return isset($_SERVER['HTTP_X_SIGNATURE']) && $_SERVER['HTTP_X_SIGNATURE'] === static::getHash();
    }
}
