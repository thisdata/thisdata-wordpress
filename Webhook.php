<?php

namespace ThisData\WordPress;
use Mohiohio\WordPress\Router;

class Webhook {

    const WEBHOOK_URL = 'thisdata/wp-user-reset';

    static function init() {

        Router::routes([

            self::WEBHOOK_URL => function() {

                \Analog::log('With $_POST '.var_export($_POST,true),\Analog::DEBUG);

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
                \Analog::log('Destroying Sessions',\Analog::DEBUG);

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
}
