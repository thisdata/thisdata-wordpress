<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Router;

class Webhook {

    const WEBHOOK_URL = 'thisdata/webhook';

    static function init() {

        Router::routes([

            self::WEBHOOK_URL => function() {

                \Analog::log('Reqest body '.file_get_contents('php://input'), \Analog::DEBUG);
                \Analog::log('Request Hash '.static::getHash(), \Analog::DEBUG);
                \Analog::log('Header Hash '.isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : " ( not found ) " , \Analog::DEBUG);

                if(!static::authenticate()) {
                    \Analog::log('Webhook failed to autenticate', \Analog::DEBUG);
                    header('HTTP/1.0 401 Unauthorized');
                    exit();
                }

                \Analog::log('Webhook Autenticated', \Analog::DEBUG);

                $data = json_decode(static::getRequestBody(),true);

                $user_id = $data['user']['id'];
                $was_user = $data['was_user'];

                \Analog::log('User ID'.$user_id, \Analog::DEBUG);
                \Analog::log('Was User'.$was_user, \Analog::DEBUG);

                if(!$user = get_userdata($user_id)) {
                    \Analog::log('No user found', \Analog::DEBUG);
                    return false; //No such user
                }

                if($was_user) {
                    \Analog::log('Returning as user confirmed it was them', \Analog::DEBUG);
                    return; //All Good, user confirmed that this was them
                }

                //Track this event ( custom event )
                Events::track([
                    'verb' => 'webhook-reset',
                    'eventEndpoint' => API::getEventsEndpoint(),
                    'user' => $user
                ]);

                //Destory sessoins,
                \Analog::log('Destroying Session', \Analog::DEBUG);
                $sessions = \WP_Session_Tokens::get_instance( $user_id );
                $sessions->destroy_all();

                //Create new password
                \Analog::log('Creating new password', \Analog::DEBUG);
                wp_set_password(wp_generate_password(), $user_id);

                $key = get_password_reset_key($user);

                //Email user with Reset password link
                \Analog::log('Emailing user with reset password link', \Analog::DEBUG);
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
