<?php

namespace ThisData\WordPress;

class Events {

    /**
    * Thisdata verbs to WordPress actions
    *
    * ThisData verbs here
    * http://help.thisdata.com/v2.0/docs/verbs
    *
    * WordPress actions here
    * https://developer.wordpress.org/
    *
    * Thisdata verbs to WordPress actions
    */
    static $events = [
        'log-in' => 'wp_login',
        'log-out' => 'wp_logout',
        'log-in-denied' => 'wp_login_failed',
        'password-reset-request' => 'retrieve_password_key',
        'password-reset' => 'after_password_reset',

        //'password-reset-fail' => // Can't find a hook that will give me access to the user in this case
        'email-update' => 'profile_update',
        'password-update' => 'profile_update',

        'plugin-activated' => 'activate_plugin',
        'plugin-deactivated' => 'deactivate_plugin',
        'automatic-update' => 'upgrader_process_complete'

    ];

    static function init(\ThisData\Api\Endpoint\EventsEndpoint $endpoint) {

        //User fields at time of init
        $user = static::getCurrentUser();

        //Loop over each event we are interested in and call 'track' or track{camelCase(verb)}
        foreach(static::$events as $verb => $wpHook) {

            add_action( $wpHook, function() use ($wpHook, $verb, $endpoint, $user) {

                //Look for ~ `trackLogIn`
                $method = 'track'.str_replace('-', '', ucwords($verb, '-'));

                //Default to `track`
                if(!method_exists(get_called_class(),$method)){
                    $method = 'track';
                }

                static::$method([
                    'verb' => $verb,
                    'eventEndpoint' => $endpoint,
                    'hook' => $wpHook,
                    'hookArgs' => func_get_args(),
                    'userAtInit' => $user
                ]);
            },10,10);
        }
    }

    static function getCurrentUser() {
        return get_current_user_id() ? wp_get_current_user() : null;
    }

    static function trackLogIn($args) {

        // @see https://developer.wordpress.org/reference/hooks/wp_login/
        list($username, $user) = $args['hookArgs'];

        if( empty($username) || !($user instanceof \WP_User) ) {
            //This should never happen.  @see wp-includes/user.php signon
            //But note that if a plugin returned something other than WP_Error on
            //wp_authenticate ( null for example ) then we would be in trouble.
            //Without this check, we would record an anonymous login, which isn't correct
            //\Analog::log('Invalid arguments passed from wp_login hook', \Analog::DEBUG);
            return null;
        }

        return static::userNameHook($args);
    }

    static function trackLogOut($args) {
        return static::track([
             // user has already logged out at this point so pass user from init
            'user' => $args['userAtInit']
        ] + $args);
    }

    static function trackLogInDenied($args) {
        // First argument to hook is username that failed
        // https://developer.wordpress.org/reference/hooks/wp_login_failed/
        return static::userNameHook($args);
    }

    static function trackPasswordResetRequest($args) {
        // First argument to hook is username
        // https://developer.wordpress.org/reference/hooks/retrieve_password_key/
        return static::userNameHook($args);
    }

    static function trackPasswordReset($args) {
        // First argument to hook is user object
        // https://developer.wordpress.org/reference/hooks/after_password_reset/
        static::track([
            'user' => $args['hookArgs'][0]
        ] + $args);
    }

    static function trackEmailUpdate($args) {
        return static::profileUpdateCompare($args, function($user, $old_user) {
             return $user->user_email != $old_user->user_email;
        });
    }

    static function trackPasswordUpdate($args) {
        return static::profileUpdateCompare($args, function($user, $old_user) {
             return $user->user_pass != $old_user->user_pass;
        });
    }

    static function profileUpdateCompare($args, callable $compare) {
        //https://developer.wordpress.org/reference/hooks/profile_update/
        list($user_id, $old_user_data) = $args['hookArgs'];

        $user = get_user_by('id',$user_id);

        if($compare($user, $old_user_data)) {
            static::track([
                'user' => $user ?: null
            ] + $args);
        }
    }

    static function trackPluginActivated($args) {
        return static::currentUserHook($args);
    }

    static function trackPluginDeactivated($args) {
        return static::currentUserHook($args);
    }

    static function trackAutomaticUpdate($args) {
        return static::currentUserHook($args);
    }

    //Track hooks where first argument is the username attempting the action
    static function userNameHook($args) {
        return static::track([
            'username' => $args['hookArgs'][0]
        ] + $args);
    }

    //Track hooks based on current user
    static function currentUserHook($args) {
        return static::track([
            'user' => static::getCurrentUser()
        ] + $args);
    }

    static function track($args) {

        $args += [
            'verb' => '__required',
            'eventEndpoint' => '__required',
            'hookArgs' => [],
            'user' => null,
            'username' => null,
        ];

        foreach($args as $k => $v) {
            if($v === '__required') {
                throw new \Exception('Missing argument '.$k);
            }
        }

        extract($args);

        $userData = static::getUser($user, $username);

        //\Analog::log('Tracking Event '.$verb.' with '.var_export($userData,true),\Analog::DEBUG);
        return $eventEndpoint->trackEvent($verb,
            static::getIP(),
            $userData,
            static::getUserAgent()
        );
    }

    static function getIP() {
        return $_SERVER['REMOTE_ADDR'];
    }

    static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    static function getAnonymousUser($username=null) {
        return [
            'id' => $username ?: 'anonymous'
        ];
    }

    static function getUser(\WP_User $user = null, $username=null) {

        if (!$user && $username) {
            $user = get_user_by('login',$username);
        }

        return $user ? [
                'id' => $user->user_login,
                'name' => static::getUserName($user),
                'email' => $user->user_email,
            ] : static::getAnonymousUser($username);
    }

    static function getUserName($user) {

        if($user->first_name && $user->last_name){
            return $user->first_name.' '.$user->last_name;
        }
        return $user->display_name;
    }
}
