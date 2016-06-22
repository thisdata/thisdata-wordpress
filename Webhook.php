<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Router;

class Webhook {

    const WEBHOOK_URL = 'thisdata/webhook';

    static function init() {

        Router::routes([

            self::WEBHOOK_URL => function() {

                \Analog::log('Reqest body: '.file_get_contents('php://input'), \Analog::DEBUG);
                \Analog::log('Request Hash: '.static::getHash(), \Analog::DEBUG);
                \Analog::log('Header Hash: '. ( isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : " ( not found ) "  ) , \Analog::DEBUG);

                if(!static::authenticate()) {
                    \Analog::log('Webhook failed to authenticate', \Analog::DEBUG);
                    header('HTTP/1.0 401 Unauthorized');
                    exit();
                }

                \Analog::log('Webhook authenticated.', \Analog::DEBUG);

                $data = json_decode(static::getRequestBody(),true);

                $username = $data['user']['id'];
                $was_user = $data['was_user'];

                \Analog::log('User ID: '.$username, \Analog::DEBUG);
                \Analog::log('Was User: '.var_export($was_user,true), \Analog::DEBUG);

                if(!$user = get_user_by('login',$username)) {
                    \Analog::log('No user found', \Analog::DEBUG);
                    return false; //No such user
                }

                $user_id = $user->ID;

                if($was_user === true) {

                    Events::track([
                        'verb' => 'webhook-was-user',
                        'eventEndpoint' => API::getEventsEndpoint(),
                        'user' => $user
                    ]);
                }
                else if($was_user === false) {

                    Events::track([
                        'verb' => 'webhook-resetting-password',
                        'eventEndpoint' => API::getEventsEndpoint(),
                        'user' => $user
                    ]);

                    //Destory sessoins,
                    \Analog::log('Destroying session', \Analog::DEBUG);
                    $sessions = \WP_Session_Tokens::get_instance($user_id);
                    $sessions->destroy_all();

                    //Create new password
                    \Analog::log('Creating new password', \Analog::DEBUG);
                    wp_set_password(wp_generate_password(), $user_id);

                    $key = get_password_reset_key($user);

                    //Email user with Reset password link
                    \Analog::log('Emailing user with reset password link', \Analog::DEBUG);
                    Email::passwordReset($user, $key);

                } else {

                    Events::track([
                        'verb' => 'webhook-login-anomaly',
                        'eventEndpoint' => API::getEventsEndpoint(),
                        'user' => $user
                    ]);
                }
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
